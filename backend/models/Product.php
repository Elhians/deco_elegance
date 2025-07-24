<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $slug;
    public $description;
    public $price;
    public $stock;
    public $image;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les produits
    public function readAll() {
        $query = "SELECT p.*, c.name as category_name 
                    FROM " . $this->table_name . " p
                    LEFT JOIN product_categories c ON p.category_id = c.id
                    ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Lire un produit par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->name = $row['name'];
        $this->slug = $row['slug'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->stock = $row['stock'];
        $this->image = $row['image'];
        $this->category_id = $row['category_id'];
        $this->created_at = $row['created_at'];
    }

    // Lire les produits par catégorie
    public function readByCategory($category_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();
        
        return $stmt;
    }

    //Mettre a jour un produit
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name,
                    slug = :slug,
                    description = :description,
                    price = :price,
                    stock = :stock,
                    image = :image,
                    category_id = :category_id
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Liaison des paramètres
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>