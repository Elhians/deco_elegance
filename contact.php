<?php require_once 'inc/header.php'; ?>
<?php
$serviceChoisi = isset($_GET['service']) ? htmlspecialchars($_GET['service']) : '';
$reservationReussie = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $sujet = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $serviceChoisi = $_POST['service'] ?? '';

    // Enregistrement (à adapter)
    $reservationReussie = true;
}
?>
<main class="section">
    <div class="container">
        <div class="section-title"><h2>Contactez-nous</h2></div>

        <?php if ($reservationReussie): ?>
            <div class="alert-success" style="margin-top: 20px; padding: 10px; background: #d4edda; color: #155724;">
                Réservation envoyée avec succès pour le service : <strong><?php echo $serviceChoisi; ?></strong>
            </div>
        <?php endif; ?>

        <?php if (!empty($serviceChoisi)): ?>
            <p><strong>Service sélectionné :</strong> <?php echo $serviceChoisi; ?></p>
        <?php endif; ?>

        <div class="contact-container">
            <div class="contact-info">
                <h3>Coordonnées</h3>
                <div class="contact-details">
                    <div class="contact-item"><i class="fas fa-map-marker-alt contact-icon"></i><div><p>123 Avenue de la Décoration<br>75001 Paris, France</p></div></div>
                    <div class="contact-item"><i class="fas fa-phone-alt contact-icon"></i><div><p>+33 1 23 45 67 89</p></div></div>
                    <div class="contact-item"><i class="fas fa-envelope contact-icon"></i><div><p>contact@deco-elegance.com</p></div></div>
                </div>
            </div>
            <div class="contact-form">
                <form method="POST">
                    <input type="hidden" name="service" value="<?php echo $serviceChoisi; ?>">
                    <div><label for="name">Nom complet</label><input type="text" id="name" name="name" required></div>
                    <div><label for="email">Email</label><input type="email" id="email" name="email" required></div>
                    <div><label for="subject">Sujet</label><input type="text" id="subject" name="subject" required></div>
                    <div><label for="message">Message</label><textarea id="message" name="message" required></textarea></div>
                    <button type="submit" class="btn">Envoyer le message</button>
                </form>
            </div>
        </div>
    </div>
</main>
<?php require_once 'inc/footer.php'; ?>
