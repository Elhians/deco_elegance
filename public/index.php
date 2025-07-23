<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../backend/controllers/ProductController.php';
require_once '../backend/controllers/ServiceController.php';
require_once '../backend/controllers/UserController.php';
require_once '../backend/controllers/OrderController.php';
require_once '../backend/controllers/AppointmentController.php';
require_once '../backend/config/database.php';

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

// Simple routing
switch (true) {
    // Produits
    case preg_match('/\/api\/products$/', $request) && $method == 'GET':
        $controller = new ProductController();
        $products = $controller->getAllProducts();
        echo json_encode($products);
        break;
        
    case preg_match('/\/api\/products\/(\d+)$/', $request, $matches) && $method == 'GET':
        $controller = new ProductController();
        $product = $controller->getProductById($matches[1]);
        echo json_encode($product);
        break;
        
    case preg_match('/\/api\/categories\/(\d+)\/products$/', $request, $matches) && $method == 'GET':
        $controller = new ProductController();
        $products = $controller->getProductsByCategory($matches[1]);
        echo json_encode($products);
        break;
        
    // Services
    case preg_match('/\/api\/services$/', $request) && $method == 'GET':
        $controller = new ServiceController();
        $services = $controller->getAllServices();
        echo json_encode($services);
        break;
        
    // Utilisateurs
    case preg_match('/\/api\/register$/', $request) && $method == 'POST':
        $controller = new UserController();
        $controller->register($data);
        break;
        
    case preg_match('/\/api\/login$/', $request) && $method == 'POST':
        $controller = new UserController();
        $controller->login($data['email'] ?? '', $data['password'] ?? '');
        break;
        
    case preg_match('/\/api\/profile$/', $request) && $method == 'GET':
        // Vérifier si l'utilisateur est connecté
        loginRequired();
        $controller = new UserController();
        $controller->getUserProfile(getUserID());
        break;
        
    // Commandes
    case preg_match('/\/api\/orders$/', $request) && $method == 'POST':
        loginRequired();
        $controller = new OrderController();
        $controller->createOrder(getUserID(), $data['items'] ?? []);
        break;
        
    case preg_match('/\/api\/orders$/', $request) && $method == 'GET':
        loginRequired();
        $controller = new OrderController();
        $controller->getUserOrders(getUserID());
        break;
        
    // Rendez-vous
    case preg_match('/\/api\/appointments$/', $request) && $method == 'POST':
        loginRequired();
        $controller = new AppointmentController();
        $controller->createAppointment(getUserID(), $data);
        break;
        
    case preg_match('/\/api\/appointments\/check$/', $request) && $method == 'GET':
        $controller = new AppointmentController();
        $controller->checkAvailability($_GET['service_id'] ?? 0, $_GET['date'] ?? '');
        break;
        
    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found"));
        break;
}
?>