<?php
session_start();
require_once 'inc/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($product_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de produit invalide'
        ]);
        exit;
    }
    
    // Valider que le produit existe et a du stock
    require_once 'backend/controllers/ProductController.php';
    $productController = new ProductController();
    $product = $productController->getProductById($product_id);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Produit non trouvé'
        ]);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuffisant'
        ]);
        exit;
    }
    
    // Ajouter au panier
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Récupérer le nouveau compte
    $count = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $count += $qty;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Produit ajouté au panier',
        'cart_count' => $count
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}