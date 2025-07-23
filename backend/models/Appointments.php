<?php
class ServiceAppointment {
    private $conn;
    private $table_name = "service_appointments";

    public $id;
    public $user_id;
    public $service_id;
    public $appointment_date;
    public $notes;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un rendez-vous
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET user_id = :user_id,
                    service_id = :service_id,
                    appointment_date = :appointment_date,
                    notes = :notes,
                    status = 'pending'";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->service_id = htmlspecialchars(strip_tags($this->service_id));
        $this->appointment_date = htmlspecialchars(strip_tags($this->appointment_date));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Liaison des paramètres
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":service_id", $this->service_id);
        $stmt->bindParam(":appointment_date", $this->appointment_date);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Lire les rendez-vous d'un utilisateur
    public function readByUser($user_id) {
        $query = "SELECT a.*, s.name as service_name
                  FROM " . $this->table_name . " a
                  JOIN services s ON a.service_id = s.id
                  WHERE a.user_id = ?
                  ORDER BY a.appointment_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    // Mettre à jour le statut d'un rendez-vous
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Liaison des paramètres
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Vérifier la disponibilité d'une date pour un service
    public function isDateAvailable($service_id, $date) {
        $query = "SELECT id FROM " . $this->table_name . "
                WHERE service_id = :service_id
                AND DATE(appointment_date) = DATE(:appointment_date)
                AND status != 'cancelled'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->bindParam(":appointment_date", $date);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }
}
?>