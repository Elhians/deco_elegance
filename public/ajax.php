<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__.'/../../backend/includes/auth.php';
require_once __DIR__.'/../../backend/config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Action inconnue'];

try {
    switch ($action) {
        // Gestion du panier
        case 'add_to_cart':
            require_once __DIR__.'/../../backend/controllers/CartController.php';
            $controller = new CartController();
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $response = $controller->addToCart($product_id, $quantity);
            break;

        case 'get_cart':
            require_once __DIR__.'/../../backend/controllers/CartController.php';
            $controller = new CartController();
            $response = $controller->getCartDetails();
            break;

        case 'update_cart_item':
            require_once __DIR__.'/../../backend/controllers/CartController.php';
            $controller = new CartController();
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $response = $controller->updateCartItem($product_id, $quantity);
            break;

        case 'remove_from_cart':
            require_once __DIR__.'/../../backend/controllers/CartController.php';
            $controller = new CartController();
            $product_id = (int)($_POST['product_id'] ?? 0);
            $response = $controller->removeFromCart($product_id);
            break;

        case 'place_order':
            loginRequired();
            require_once __DIR__.'/../../backend/controllers/OrderController.php';
            $controller = new OrderController();
            $shipping_address = $_POST['shipping_address'] ?? '';
            $response = $controller->createOrderFromCart(getUserID(), $shipping_address);
            break;

        case 'get_order_details':
            loginRequired();
            require_once __DIR__.'/../../backend/controllers/OrderController.php';
            $controller = new OrderController();
            $order_id = (int)($_GET['order_id'] ?? 0);
            $response = $controller->getUserOrderDetails(getUserID(), $order_id);
            break;

        case 'get_user_orders':
            loginRequired();
            require_once __DIR__.'/../../backend/controllers/OrderController.php';
            $controller = new OrderController();
            $status = $_GET['status'] ?? null;
            $response = $controller->getUserOrdersWithStatus(getUserID(), $status);
            break;

        default:
            http_response_code(404);
            $response['message'] = 'Action non reconnue';
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>