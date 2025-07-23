<?php
require_once '../models/Product.php';
require_once '../config/database.php';

class ProductController {
    private $db;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
    }

    public function getAllProducts() {
        $stmt = $this->product->readAll();
        $products_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $product_item = array(
                "id" => $id,
                "name" => $name,
                "description" => html_entity_decode($description),
                "price" => $price,
                "category_id" => $category_id,
                "category_name" => $category_name,
                "image" => $image
            );
            array_push($products_arr, $product_item);
        }
        
        return $products_arr;
    }

    public function getProductById($id) {
        $this->product->id = $id;
        $this->product->readOne();
        
        $product_arr = array(
            "id" => $this->product->id,
            "name" => $this->product->name,
            "description" => html_entity_decode($this->product->description),
            "price" => $this->product->price,
            "stock" => $this->product->stock,
            "category_id" => $this->product->category_id,
            "image" => $this->product->image
        );
        
        return $product_arr;
    }

    public function getProductsByCategory($category_id) {
        $stmt = $this->product->readByCategory($category_id);
        $products_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $product_item = array(
                "id" => $id,
                "name" => $name,
                "price" => $price,
                "image" => $image
            );
            array_push($products_arr, $product_item);
        }
        
        return $products_arr;
    }
}
?>