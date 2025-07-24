<?php require_once 'inc/header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-title">
            <h2>À propos de nous</h2>
        </div>
        <div class="about-container">
            <div class="about-content">
                <h2>Notre histoire</h2>
                <p>Fondée en 2010, Déco Élégance est née de l'envie de rendre la décoration intérieure accessible à tous, sans compromis sur la qualité et l'esthétique.</p>
            </div>
            <div class="about-image"><img src="https://images.unsplash.com/photo-1606744837616-56c9a5c6a6eb?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Notre équipe"></div>
        </div>
        <div class="team-container">
            <h2 style="text-align: center; width: 100%; margin-top: 50px; margin-bottom: 20px;">Notre équipe</h2>
            <?php foreach (get_team_members() as $membre): ?>
            <div class="team-member">
                <img src="<?php echo htmlspecialchars($membre['image']); ?>" alt="<?php echo htmlspecialchars($membre['nom']); ?>">
                <h3><?php echo htmlspecialchars($membre['nom']); ?></h3>
                <p><?php echo htmlspecialchars($membre['role']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require_once 'inc/footer.php'; ?>