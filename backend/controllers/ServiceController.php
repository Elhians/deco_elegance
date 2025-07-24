<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class ServiceController {
    private $db;
    private $service;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->service = new Service($this->db);
    }

    // Récupérer tous les services
    public function getAllServices() {
        $stmt = $this->service->readAll();
        $services = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'duration' => $row['duration'],
                'icone' => $row['icone'] ?? 'fa-star', // Valeur par défaut
                'titre' => $row['name'] // Utilisez 'name' comme 'titre' pour la compatibilité
            ];
        }
        
        // Retourner les données au lieu de les envoyer directement
        return $services;
    }

    // Récupérer un service spécifique
    public function getService($id) {
        $this->service->id = $id;
        $this->service->readOne();
        
        if($this->service->name != null) {
            $service = [
                'id' => $id,
                'name' => $this->service->name,
                'description' => $this->service->description,
                'price' => $this->service->price,
                'duration' => $this->service->duration
            ];
            
            jsonResponse(true, '', $service);
        } else {
            jsonResponse(false, 'Service non trouvé.');
        }
    }
    
    // Rechercher des services
    public function searchServices($keyword) {
        $query = "SELECT * FROM services 
                    WHERE name LIKE ? OR description LIKE ? 
                    ORDER BY name";
                    
        $stmt = $this->db->prepare($query);
        $searchTerm = "%{$keyword}%";
        $stmt->bindParam(1, $searchTerm);
        $stmt->bindParam(2, $searchTerm);
        $stmt->execute();
        
        $services = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'duration' => $row['duration']
            ];
        }
        
        jsonResponse(true, '', $services);
    }
}
?>