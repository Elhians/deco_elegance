<?php
    require_once __DIR__ . '/../models/Order.php';
    require_once __DIR__ . '/../models/Product.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';

class OrderController {
    private $db;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }
    
    // Créer une commande à partir du panier
    public function createOrderFromCart($user_id, $shipping_address = null) {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return ['success' => false, 'message' => 'Le panier est vide'];
        }

        $cart_items = $_SESSION['cart'];
        
        try {
            $this->db->beginTransaction();
            
            // Créer la commande
            $this->order->user_id = $user_id;
            $this->order->total = 0;
            $this->order->status = 'En attente';
            
            // Calculer le total et valider le stock
            $items_for_order = [];
            $total = 0;
            
            foreach ($cart_items as $product_id => $quantity) {
                $product = new Product($this->db);
                $product->id = $product_id;
                
                if (!$product->readOne()) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Produit non trouvé: ID ' . $product_id];
                }
                
                if ($product->stock < $quantity) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Stock insuffisant pour: ' . $product->name];
                }
                
                $item_total = $product->price * $quantity;
                $items_for_order[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $item_total
                ];
                
                $total += $item_total;
            }
            
            $this->order->total = $total;
            $order_id = $this->order->create();
            
            if (!$order_id) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Erreur lors de la création de la commande'];
            }
            
            // Ajouter les articles à la commande
            foreach ($items_for_order as $item) {
                if (!$this->order->addOrderItem($order_id, $item['product_id'], $item['quantity'], $item['price'])) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Erreur lors de l\'ajout des articles à la commande'];
                }
                
                // Mettre à jour le stock
                $product = new Product($this->db);
                $product->id = $item['product_id'];
                $product->readOne();
                $product->stock -= $item['quantity'];
                $product->update();
            }
            
            $this->db->commit();
            return ['success' => true, 'order_id' => $order_id];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // Récupérer les commandes d'un utilisateur avec leur statut
    public function getUserOrdersWithStatus($user_id) {
        $stmt = $this->order->readByUser($user_id);
        
        if(!$stmt) {
            return ['success' => false, 'message' => 'Erreur lors de la récupération des commandes'];
        }
        
        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = [
                'id' => $row['id'],
                'total' => $row['total'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'items_count' => $row['items_count'] ?? 0
            ];
        }
        
        return ['success' => true, 'orders' => $orders];
    }

    // Créer une nouvelle commande
    public function createOrder($user_id, $items) {
        // Valider les articles
        if(empty($items)) {
            jsonResponse(false, 'Le panier est vide.');
        }

        // Calculer le total et vérifier le stock
        $total = 0;
        $product = new Product($this->db);
        
        foreach($items as &$item) {
            $product->id = $item['product_id'];
            $product->readOne();
            
            if($product->stock < $item['quantity']) {
                jsonResponse(false, 'Stock insuffisant pour le produit: ' . $product->name);
            }
            
            $item['price'] = $product->price;
            $total += $product->price * $item['quantity'];
        }

        // Créer la commande
        $this->order->user_id = $user_id;
        $this->order->total = $total;
        $this->order->items = $items;

        if($this->order->create()) {
            // Mettre à jour les stocks (à implémenter)
            // $this->updateProductStocks($items);
            
            jsonResponse(true, 'Commande passée avec succès.', [
                'order_id' => $this->order->id,
                'total' => $total
            ]);
        } else {
            jsonResponse(false, 'Erreur lors de la création de la commande.');
        }
    }

    // Récupérer les commandes d'un utilisateur
    public function getUserOrders($user_id) {
        $stmt = $this->order->readByUser($user_id);
        $orders = array();
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $row;
        }
        
        jsonResponse(true, '', $orders);
    }

    // Récupérer les détails d'une commande
    public function getOrderDetails($order_id, $user_id) {
        $this->order->id = $order_id;
        
        if($this->order->readOne()) {
            // Vérifier que la commande appartient bien à l'utilisateur
            if($this->order->user_id != $user_id) {
                jsonResponse(false, 'Accès non autorisé à cette commande.');
            }
            
            jsonResponse(true, '', [
                'order' => [
                    'id' => $this->order->id,
                    'total' => $this->order->total,
                    'status' => $this->order->status,
                    'created_at' => $this->order->created_at
                ],
                'items' => $this->order->items
            ]);
        } else {
            jsonResponse(false, 'Commande non trouvée.');
        }
    }

    // Méthode pour mettre à jour les stocks (privée)
    private function updateProductStocks($items) {
        $product = new Product($this->db);
        foreach($items as $item) {
            $product->id = $item['product_id'];
            $product->readOne();
            
            // Mettre à jour le stock
            $product->stock -= $item['quantity'];
            if(!$product->update()) {
                jsonResponse(false, 'Erreur lors de la mise à jour du stock pour le produit: ' . $product->name);
            }
        }
    }
    public function getUserOrderDetails($user_id, $order_id) {
        $order = new Order($this->db);
        $order->id = $order_id;
        
        if (!$order->readOne()) {
            return ['success' => false, 'message' => 'Commande non trouvée'];
        }
        
        // Vérifier que la commande appartient bien à l'utilisateur
        if ($order->user_id != $user_id) {
            return ['success' => false, 'message' => 'Accès non autorisé'];
        }
        
        // Récupérer l'historique des statuts
        $status_history = [];
        $stmt = $order->getStatusHistory($order_id);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status_history[] = $row;
        }
        
        return [
            'success' => true,
            'order' => [
                'id' => $order->id,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'items' => $order->items
            ],
            'status_history' => $status_history
        ];
    }
}
?>