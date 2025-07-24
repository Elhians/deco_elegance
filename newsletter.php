<?php
// Enregistrer l'email dans une base de données ou un fichier
$email = $_POST['email'] ?? '';

// Ici vous pourriez ajouter la logique pour enregistrer l'email
// Par exemple dans un fichier :
file_put_contents('newsletter_subscribers.txt', $email . PHP_EOL, FILE_APPEND);

// Réponse JSON pour AJAX
header('Content-Type: application/json');
echo json_encode(['success' => true]);