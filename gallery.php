<?php require_once 'inc/header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-title">
            <h2>Nos Réalisations</h2>
            <p>Découvrez quelques-uns de nos projets récents et laissez-vous inspirer.</p>
        </div>
        <div class="gallery">
            <?php foreach (get_realisations() as $realisation): ?>
            <div class="gallery-item">
                <img src="<?php echo htmlspecialchars($realisation['image']); ?>" alt="<?php echo htmlspecialchars($realisation['titre']); ?>">
                <div class="gallery-overlay">
                    <h3><?php echo htmlspecialchars($realisation['titre']); ?></h3>
                    <p><?php echo htmlspecialchars($realisation['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require_once 'inc/footer.php'; ?>