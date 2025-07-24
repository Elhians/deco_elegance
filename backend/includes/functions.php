<?php
// Fonction pour valider les données d'entrée
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour générer une réponse JSON standardisée
function jsonResponse($success, $message = '', $data = array()) {
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => $success,
        'message' => $message,
        'data' => $data
    ));
    exit;
}

// Fonction pour vérifier l'authentification
function authenticateUser() {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="Déco Élégance"');
        header('HTTP/1.0 401 Unauthorized');
        jsonResponse(false, 'Authentification requise');
    } else {
        $email = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        
        $database = new Database();
        $db = $database->getConnection();
        
        $user = new User($db);
        $user->email = $email;
        
        if(!$user->emailExists() || !password_verify($password, $user->password)) {
            jsonResponse(false, 'Email ou mot de passe incorrect');
        }
        
        return $user;
    }
}

// Fonction pour gérer les uploads d'images
function uploadImage($file, $target_dir = "../public/uploads/") {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Vérifier si le fichier est une image réelle
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return array("success" => false, "message" => "Le fichier n'est pas une image.");
    }
    
    // Vérifier la taille du fichier (max 5MB)
    if ($file["size"] > 5000000) {
        return array("success" => false, "message" => "Désolé, votre fichier est trop volumineux.");
    }
    
    // Autoriser certains formats de fichier
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return array("success" => false, "message" => "Désolé, seuls les fichiers JPG, JPEG, PNG & GIF sont autorisés.");
    }
    
    // Générer un nom de fichier unique
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Déplacer le fichier uploadé
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array("success" => true, "filename" => $new_filename);
    } else {
        return array("success" => false, "message" => "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.");
    }
}
?>