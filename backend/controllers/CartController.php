<?php
require_once '../models/Cart.php';
require_once '../models/Product.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

class CartController {
    private $cart;
    private $db;

    public function __construct() {
        $this->cart = new Cart();
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function addToCart($product_id, $quantity = 1) {
        // Vérifier si le produit existe
        $product = new Product($this->db);
        $product->id = $product_id;
        
        if (!$product->readOne()) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }

        // Vérifier le stock
        if ($product->stock < $quantity) {
            return ['success' => false, 'message' => 'Stock insuffisant'];
        }

        $this->cart->addItem($product_id, $quantity);
        
        return [
            'success' => true,
            'cart_count' => $this->cart->countItems(),
            'message' => 'Produit ajouté au panier'
        ];
    }

    public function getCartDetails() {
        $items = $this->cart->getItems();
        $products = [];
        $total = 0;

        if (!empty($items)) {
            $product_ids = implode(',', array_keys($items));
            $query = "SELECT * FROM products WHERE id IN ($product_ids)";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $quantity = $items[$row['id']];
                $subtotal = $row['price'] * $quantity;
                
                $products[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'image' => $row['image']
                ];
                
                $total += $subtotal;
            }
        }

        return [
            'success' => true,
            'items' => $products,
            'total' => $total,
            'count' => $this->cart->countItems()
        ];
    }

    public function updateCartItem($product_id, $quantity) {
        $product = new Product($this->db);
        $product->id = $product_id;
        
        if (!$product->readOne()) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }

        if ($product->stock < $quantity) {
            return ['success' => false, 'message' => 'Stock insuffisant'];
        }

        $this->cart->updateItem($product_id, $quantity);
        
        return $this->getCartDetails();
    }

    public function removeFromCart($product_id) {
        $this->cart->removeItem($product_id);
        return $this->getCartDetails();
    }

    public function clearCart() {
        $this->cart->clear();
        return ['success' => true, 'message' => 'Panier vidé'];
    }
}
?>