<?php
require_once '../models/Appointment.php';
require_once '../models/Service.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

class AppointmentController {
    private $db;
    private $appointment;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->appointment = new Appointment($this->db);
    }

    // Créer un nouveau rendez-vous
    public function createAppointment($user_id, $data) {
        // Validation des données
        if(empty($data['service_id']) || empty($data['appointment_date'])) {
            jsonResponse(false, 'Tous les champs obligatoires doivent être remplis.');
        }

        // Vérifier la disponibilité de la date
        if(!$this->appointment->isDateAvailable($data['service_id'], $data['appointment_date'])) {
            jsonResponse(false, 'Cette date n\'est plus disponible. Veuillez en choisir une autre.');
        }

        $this->appointment->user_id = $user_id;
        $this->appointment->service_id = $data['service_id'];
        $this->appointment->appointment_date = $data['appointment_date'];
        $this->appointment->notes = $data['notes'] ?? null;

        if($this->appointment->create()) {
            jsonResponse(true, 'Rendez-vous enregistré avec succès.', [
                'appointment_id' => $this->appointment->id
            ]);
        } else {
            jsonResponse(false, 'Erreur lors de l\'enregistrement du rendez-vous.');
        }
    }

    // Récupérer les rendez-vous d'un utilisateur
    public function getUserAppointments($user_id) {
        $stmt = $this->appointment->readByUser($user_id);
        $appointments = array();
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $appointments[] = $row;
        }
        
        jsonResponse(true, '', $appointments);
    }

    // Annuler un rendez-vous
    public function cancelAppointment($appointment_id, $user_id) {
        // Vérifier que le rendez-vous appartient à l'utilisateur
        $query = "SELECT id FROM appointments
                    WHERE id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $appointment_id);
        $stmt->bindParam(2, $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() === 0) {
            jsonResponse(false, 'Rendez-vous non trouvé ou accès non autorisé.');
        }

        // Mettre à jour le statut
        $this->appointment->id = $appointment_id;
        $this->appointment->status = 'cancelled';
        
        if($this->appointment->updateStatus()) {
            jsonResponse(true, 'Rendez-vous annulé avec succès.');
        } else {
            jsonResponse(false, 'Erreur lors de l\'annulation du rendez-vous.');
        }
    }

    // Vérifier les disponibilités pour un service
    public function checkAvailability($service_id, $date) {
        $isAvailable = $this->appointment->isDateAvailable($service_id, $date);
        
        jsonResponse(true, '', [
            'available' => $isAvailable,
            'date' => $date,
            'service_id' => $service_id
        ]);
    }
}
?>