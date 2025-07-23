<?php
require_once '../models/User.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // Inscription d'un nouvel utilisateur
    public function register($data) {
        // Validation des données
        if(empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            jsonResponse(false, 'Tous les champs obligatoires doivent être remplis.');
        }

        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Format d\'email invalide.');
        }

        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->address = $data['address'] ?? null;
        $this->user->phone = $data['phone'] ?? null;

        // Vérifier si l'email existe déjà
        if($this->user->emailExists()) {
            jsonResponse(false, 'Cet email est déjà utilisé.');
        }

        // Créer l'utilisateur
        if($this->user->create()) {
            jsonResponse(true, 'Inscription réussie. Vous pouvez maintenant vous connecter.');
        } else {
            jsonResponse(false, 'Erreur lors de l\'inscription. Veuillez réessayer.');
        }
    }


    // Récupérer les informations d'un utilisateur
    public function getUserProfile($user_id) {
        $this->user->id = $user_id;
        
        $query = "SELECT id, email, first_name, last_name, address, phone, created_at 
                    FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            jsonResponse(true, '', $row);
        } else {
            jsonResponse(false, 'Utilisateur non trouvé.');
        }
    }

    // Mettre à jour le profil utilisateur
    public function updateProfile($user_id, $data) {
        $this->user->id = $user_id;
        $this->user->first_name = $data['first_name'] ?? null;
        $this->user->last_name = $data['last_name'] ?? null;
        $this->user->address = $data['address'] ?? null;
        $this->user->phone = $data['phone'] ?? null;

        if($this->user->update()) {
            jsonResponse(true, 'Profil mis à jour avec succès.');
        } else {
            jsonResponse(false, 'Erreur lors de la mise à jour du profil.');
        }
    }

    // Méthode de connexion simplifiée
    public function login($email, $password) {
        $this->user->email = $email;

        if ($this->user->emailExists() && password_verify($password, $this->user->password)) {
            // Créer la session
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['user_email'] = $this->user->email;
            
            return [
                'success' => true,
                'user' => [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                    'first_name' => $this->user->first_name
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }

    // Méthode de déconnexion
    public function logout() {
        session_destroy();
        return ['success' => true];
    }
}
?>