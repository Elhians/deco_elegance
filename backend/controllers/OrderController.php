<?php
require_once '../models/Order.php';
require_once '../models/Product.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

class OrderController {
    private $db;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }
    public function createOrderFromCart($user_id, $shipping_address) {
        $cart = new Cart();
        $cart_items = $cart->getItems();
        
        if (empty($cart_items)) {
            return ['success' => false, 'message' => 'Le panier est vide'];
        }

        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();

            // Calculer le total et vérifier les stocks
            $total = 0;
            $order_items = [];
            $product = new Product($db);
            
            foreach ($cart_items as $product_id => $quantity) {
                $product->id = $product_id;
                $product->readOne();
                
                if ($product->stock < $quantity) {
                    throw new Exception("Stock insuffisant pour: {$product->name}");
                }
                
                $subtotal = $product->price * $quantity;
                $total += $subtotal;
                
                $order_items[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'price' => $product->price
                ];
            }

            // Créer la commande
            $order = new Order($db);
            $order->user_id = $user_id;
            $order->total = $total;
            $order->items = $order_items;
            
            if (!$order->create()) {
                throw new Exception("Erreur lors de la création de la commande");
            }

            // Mettre à jour les stocks
            foreach ($cart_items as $product_id => $quantity) {
                $query = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$quantity, $product_id]);
            }

            $db->commit();
            $cart->clear();
            
            return [
                'success' => true,
                'order_id' => $order->id,
                'total' => $total,
                'message' => 'Commande passée avec succès'
            ];
            
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

    public function getUserOrdersWithStatus($user_id, $status = null) {
        $query = "SELECT o.id, o.total, o.status, o.created_at, 
                        COUNT(i.id) as items_count
                FROM orders o
                LEFT JOIN order_items i ON o.id = i.order_id
                WHERE o.user_id = ?";
        
        if ($status) {
            $query .= " AND o.status = ?";
        }
        
        $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        
        if ($status) {
            $stmt->bindParam(2, $status);
        }
        
        $stmt->execute();
        
        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $row;
        }
        
        return ['success' => true, 'orders' => $orders];
    }
}
?>