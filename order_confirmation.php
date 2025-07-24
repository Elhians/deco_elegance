<?php
require_once 'inc/header.php';

if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    // Rediriger vers la page d'accueil si l'utilisateur n'a pas passé de commande
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['last_order_id'] ?? 0;

// Nettoyer les variables de session pour éviter les doubles confirmations
unset($_SESSION['order_success']);
unset($_SESSION['last_order_id']);
?>

<section class="section order-confirmation">
    <div class="container">
        <div class="confirmation-content">
            <div class="icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Commande confirmée!</h2>
            <p>Votre commande #<?php echo $order_id; ?> a été placée avec succès.</p>
            <p>Nous vous enverrons un e-mail de confirmation avec les détails de votre commande.</p>
            <p>Vous pouvez suivre l'état de votre commande dans la section <a href="account.php?tab=orders">Mes commandes</a> de votre compte.</p>
            
            <div class="confirmation-actions">
                <a href="index.php" class="btn">Retour à l'accueil</a>
                <a href="account.php?tab=orders" class="btn btn-outline">Mes commandes</a>
            </div>
        </div>
    </div>
</section>

<style>
.order-confirmation {
    padding: 60px 0;
}
.confirmation-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
    padding: 40px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.icon-success {
    font-size: 80px;
    color: #4CAF50;
    margin-bottom: 20px;
}
.confirmation-content h2 {
    color: #333;
    margin-bottom: 20px;
}
.confirmation-content p {
    color: #666;
    margin-bottom: 15px;
}
.confirmation-actions {
    margin-top: 30px;
}
.confirmation-actions .btn {
    margin: 0 10px;
}
</style>

<?php require_once 'inc/footer.php'; ?>