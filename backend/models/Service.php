<?php
class Service {
    private $conn;
    private $table_name = "services";

    public $id;
    public $name;
    public $description;
    public $price;
    public $duration;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les services
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Lire un service par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->duration = $row['duration'];
    }
}
?>