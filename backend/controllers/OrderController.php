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
}
?>