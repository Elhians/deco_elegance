<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function loginRequired() {
    if (!isLoggedIn()) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['success' => false, 'message' => 'Authentification requise']);
        exit;
    }
}

function getUserID() {
    return $_SESSION['user_id'] ?? null;
}
?>