<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $address;
    public $phone;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un utilisateur
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET email = :email,
                    password = :password,
                    first_name = :first_name,
                    last_name = :last_name,
                    address = :address,
                    phone = :phone";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        // Hash du mot de passe
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Liaison des paramètres
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Vérifier si l'email existe
    public function emailExists() {
        $query = "SELECT id, first_name, last_name, password
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->password = $row['password'];

            return true;
        }

        return false;
    }

    // Mettre à jour les informations utilisateur
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET first_name = :first_name,
                    last_name = :last_name,
                    address = :address,
                    phone = :phone
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Liaison des paramètres
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>