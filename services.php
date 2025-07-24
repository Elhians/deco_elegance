<?php 
    require_once 'inc/header.php';
    require_once 'backend/controllers/ServiceController.php';
    ?>
<main class="section">
    <div class="container">
        <div class="section-title">
            <h2>Nos Services</h2>
            <p>Nous proposons des services sur mesure pour répondre à tous vos besoins.</p>
        </div>
        <div class="services-container">
            <?php 
            $serviceController = new ServiceController();
            $services = $serviceController->getAllServices();
            
            if (empty($services)): ?>
                <div class="empty-state">
                    <i class="fas fa-concierge-bell"></i>
                    <p>Aucun service disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-icon"><i class="fas <?php echo htmlspecialchars($service['icone']); ?>"></i></div>
                        <h3><?php echo htmlspecialchars($service['titre']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <a href="contact.php?service=<?php echo urlencode($service['titre']); ?>" class="btn btn-outline" style="margin-top: 20px;">Nous contacter</a>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require_once 'inc/footer.php'; ?>