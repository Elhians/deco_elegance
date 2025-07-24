<?php
require_once 'data.php';

// Fonctions de récupération de données
function get_products() { 
    global $produits; 
    return $produits; 
}

function get_services() { 
    global $services; 
    return $services; 
}

function get_realisations() { 
    global $realisations; 
    return $realisations; 
}

function get_articles() { 
    global $articlesBlog; 
    return $articlesBlog; 
}

function get_team_members() { 
    global $equipe; 
    return $equipe; 
}

function get_accounts() {
    global $comptes;
    return $comptes;
}

function get_addresses() {
    global $adresses;
    return $adresses;
}

function get_orders() {
    global $commandes;
    return $commandes;
}

// Fonctions de recherche
function get_product_by_id($id) {
    global $produits;
    foreach ($produits as $product) {
        if ($product['id'] == $id) return $product;
    }
    return null;
}

function get_service_by_id($id) {
    global $services;
    foreach ($services as $service) {
        if ($service['id'] == $id) return $service;
    }
    return null;
}

function get_account_by_id($id) {
    global $comptes;
    foreach ($comptes as $compte) {
        if ($compte['id'] == $id) return $compte;
    }
    return null;
}

// Fonctions de panier
function get_cart_items_details() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return ['items' => [], 'total' => 0, 'count' => 0];
    }

    $cart_items = $_SESSION['cart'];
    $total = 0;
    $items = [];

    require_once 'backend/config/database.php';
    require_once 'backend/controllers/ProductController.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $productController = new ProductController();

    foreach ($cart_items as $product_id => $quantity) {
        $product = $productController->getProductById($product_id);
        
        if ($product) {
            // Suppression du mapping qui causait l'erreur
            
            $subtotal = $product['price'] * $quantity; // Utilisation de 'price' au lieu de 'prix'
            $product['quantity'] = $quantity;
            $product['subtotal'] = $subtotal;
            $items[] = $product;
            $total += $subtotal;
        }
    }

    return [
        'items' => $items,
        'total' => $total,
        'count' => array_sum($cart_items)
    ];
}

function get_cart_count() {
    return array_sum($_SESSION['cart'] ?? []);
}

function get_cart_total() {
    $cart_data = get_cart_items_details();
    return $cart_data['total'];
}

// Fonctions de manipulation du panier
function add_to_cart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function update_cart_item($product_id, $quantity) {
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            remove_from_cart($product_id);
        }
    }
}

function clear_cart() {
    unset($_SESSION['cart']);
}

// Fonctions de filtrage
function get_products_by_category($category) {
    global $produits;
    return array_filter($produits, function($product) use ($category) {
        return $product['categorie'] === $category;
    });
}

function get_featured_products($limit = 4) {
    global $produits;
    return array_slice($produits, 0, $limit);
}

// Fonctions de gestion des comptes
function create_account($nomComplet, $email, $telephone, $motDePasse) {
    global $comptes;
    $newId = count($comptes) + 1;
    $comptes[] = [
        'id' => $newId,
        'nomComplet' => $nomComplet,
        'email' => $email,
        'telephone' => $telephone,
        'motDePasse' => password_hash($motDePasse, PASSWORD_DEFAULT)
    ];
    return $newId;
}

function update_account($id, $nomComplet, $email, $telephone) {
    global $comptes;
    foreach ($comptes as &$compte) {
        if ($compte['id'] == $id) {
            $compte['nomComplet'] = $nomComplet;
            $compte['email'] = $email;
            $compte['telephone'] = $telephone;
            return true;
        }
    }
    return false;
}

function change_password($id, $newPassword) {
    global $comptes;
    foreach ($comptes as &$compte) {
        if ($compte['id'] == $id) {
            $compte['motDePasse'] = password_hash($newPassword, PASSWORD_DEFAULT);
            return true;
        }
    }
    return false;
}

// Fonctions de gestion des adresses
function add_address($idCompte, $ligne1, $codePostal, $ville, $pays) {
    global $adresses;
    $newId = count($adresses) + 1;
    $adresses[] = [
        'id' => $newId,
        'idCompte' => $idCompte,
        'ligne1' => $ligne1,
        'codePostal' => $codePostal,
        'ville' => $ville,
        'pays' => $pays
    ];
    return $newId;
}

function update_address($id, $ligne1, $codePostal, $ville, $pays) {
    global $adresses;
    foreach ($adresses as &$address) {
        if ($address['id'] == $id) {
            $address['ligne1'] = $ligne1;
            $address['codePostal'] = $codePostal;
            $address['ville'] = $ville;
            $address['pays'] = $pays;
            return true;
        }
    }
    return false;
}

function delete_address($id) {
    global $adresses;
    $adresses = array_filter($adresses, function($address) use ($id) {
        return $address['id'] != $id;
    });
}

// Fonctions de commande
function create_order($idCompte, $idProduit, $quantite, $statut = 'En attente') {
    global $commandes;
    $newId = count($commandes) + 1;
    $commandes[] = [
        'id' => $newId,
        'idCompte' => $idCompte,
        'idProduit' => $idProduit,
        'quantite' => $quantite,
        'date' => date('Y-m-d'),
        'statut' => $statut
    ];
    return $newId;
}

function update_order_status($id, $statut) {
    global $commandes;
    foreach ($commandes as &$commande) {
        if ($commande['id'] == $id) {
            $commande['statut'] = $statut;
            return true;
        }
    }
    return false;
}

function get_orders_by_account($idCompte) {
    global $commandes;
    return array_filter($commandes, function($commande) use ($idCompte) {
        return $commande['idCompte'] == $idCompte;
    });
}

// Fonction de recherche
function search_products_and_services($query) {
    $query = strtolower($query);
    $products = array_filter(get_products(), function($product) use ($query) {
        return strpos(strtolower($product['titre']), $query) !== false || 
               strpos(strtolower($product['description']), $query) !== false;
    });
    
    $services = array_filter(get_services(), function($service) use ($query) {
        return strpos(strtolower($service['titre']), $query) !== false || 
               strpos(strtolower($service['description']), $query) !== false;
    });
    
    return ['products' => $products, 'services' => $services];
}

/* // Fonction d'authentification
function authenticate($email, $password) {
    global $comptes;
    foreach ($comptes as $compte) {
        if ($compte['email'] === $email && $compte['motDePasse'] === $password) {
            return $compte;
        }
    }
    return false;
} */


function handle_cart_actions() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $response = ['success' => false];
        
        try {
            switch ($_POST['action']) {
                case 'add':
                    if (isset($_POST['product_id'])) {
                        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                        add_to_cart($_POST['product_id'], $quantity);
                        $response = [
                            'success' => true,
                            'cart_count' => get_cart_count(),
                            'cart_total' => get_cart_total()
                        ];
                    }
                    break;
                    
                case 'update':
                    if (isset($_POST['product_id'], $_POST['quantity'])) {
                        update_cart_item($_POST['product_id'], (int)$_POST['quantity']);
                        $response = [
                            'success' => true,
                            'cart_count' => get_cart_count(),
                            'cart_total' => get_cart_total()
                        ];
                    }
                    break;
                    
                case 'remove':
                    if (isset($_POST['product_id'])) {
                        remove_from_cart($_POST['product_id']);
                        $response = [
                            'success' => true,
                            'cart_count' => get_cart_count(),
                            'cart_total' => get_cart_total()
                        ];
                    }
                    break;
                    
                case 'clear':
                    clear_cart();
                    $response = [
                        'success' => true,
                        'cart_count' => 0,
                        'cart_total' => 0
                    ];
                    break;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}


function get_user_orders($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT c.*, p.titre, p.prix, p.image 
                              FROM commandes c
                              JOIN produits p ON c.product_id = p.id
                              WHERE c.user_id = ?
                              ORDER BY c.order_date DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>



