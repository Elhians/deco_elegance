<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

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
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Format d\'email invalide.'];
        }

        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->address = $data['address'] ?? null;
        $this->user->phone = $data['phone'] ?? null;

        // Vérifier si l'email existe déjà
        if($this->user->emailExists()) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
        }

        // Créer l'utilisateur
        if($this->user->create()) {
            return ['success' => true, 'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription. Veuillez réessayer.'];
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
            return ['success' => true, 'message' => '', 'data' => $row];
        } else {
            return ['success' => false, 'message' => 'Utilisateur non trouvé.'];
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
            return ['success' => true, 'message' => 'Profil mis à jour avec succès.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du profil.'];
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

    // Vérifier le mot de passe actuel
    public function verifyPassword($user_id, $current_password) {
        $query = "SELECT password FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        
        if (password_verify($current_password, $row['password'])) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Mot de passe incorrect'];
        }
    }

    // Changer le mot de passe
    public function changePassword($user_id, $new_password) {
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt->bindParam(1, $hashed_password);
        $stmt->bindParam(2, $user_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Mot de passe changé avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors du changement de mot de passe'];
        }
    }
}
?>