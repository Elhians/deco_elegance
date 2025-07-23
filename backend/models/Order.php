<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $user_id;
    public $total;
    public $status;
    public $created_at;
    public $items = array();

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une commande
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET user_id = :user_id,
                    total = :total,
                    status = 'pending'";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->total = htmlspecialchars(strip_tags($this->total));

        // Liaison des paramètres
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":total", $this->total);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Ajouter les articles de la commande
            if(!empty($this->items)) {
                $this->addOrderItems();
            }
            
            return true;
        }

        return false;
    }

    // Ajouter des articles à la commande
    private function addOrderItems() {
        $query = "INSERT INTO order_items
                SET order_id = :order_id,
                    product_id = :product_id,
                    quantity = :quantity,
                    price = :price";

        foreach($this->items as $item) {
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":order_id", $this->id);
            $stmt->bindParam(":product_id", $item['product_id']);
            $stmt->bindParam(":quantity", $item['quantity']);
            $stmt->bindParam(":price", $item['price']);
            
            $stmt->execute();
        }
    }

    // Lire les commandes d'un utilisateur
    public function readByUser($user_id) {
        $query = "SELECT o.id, o.total, o.status, o.created_at,
                         COUNT(i.id) as items_count
                  FROM " . $this->table_name . " o
                  LEFT JOIN order_items i ON o.id = i.order_id
                  WHERE o.user_id = ?
                  GROUP BY o.id
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    // Lire une commande avec ses articles
    public function readOne() {
        // Récupérer les infos de base de la commande
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->user_id = $row['user_id'];
            $this->total = $row['total'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];

            // Récupérer les articles de la commande
            $query = "SELECT i.*, p.name as product_name, p.image
                        FROM order_items i
                        JOIN products p ON i.product_id = p.id
                        WHERE i.order_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $this->items = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->items[] = $row;
            }

            return true;
        }

        return false;
    }
    public function getStatusHistory($order_id) {
        $query = "SELECT * FROM order_status_history 
                    WHERE order_id = ? 
                    ORDER BY created_at DESC";
                    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function updateOrderStatus($order_id, $new_status, $comment = '') {
        // Vérifier que la transition de statut est valide
        $valid_transitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['shipped', 'cancelled'],
            'shipped' => ['delivered']
        ];
        
        $current_status = $this->getCurrentStatus($order_id);
        
        if (!in_array($new_status, $valid_transitions[$current_status] ?? [])) {
            return false;
        }
        
        // Mettre à jour le statut principal
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $new_status);
        $stmt->bindParam(2, $order_id);
        $stmt->execute();
        
        // Ajouter à l'historique
        $query = "INSERT INTO order_status_history 
                SET order_id = ?, status = ?, comments = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->bindParam(2, $new_status);
        $stmt->bindParam(3, $comment);
        
        return $stmt->execute();
    }

    private function getCurrentStatus($order_id) {
        $query = "SELECT status FROM orders WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['status'] ?? null;
    }
}
?>