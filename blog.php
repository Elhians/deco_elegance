<?php require_once 'inc/header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-title">
            <h2>Blog & Conseils</h2>
            <p>Découvrez nos articles sur les tendances déco, astuces et inspirations.</p>
        </div>
        <div class="blog-container">
            <?php foreach (get_articles() as $article): ?>
            <div class="blog-card">
                <div class="blog-image"><img src="<?php echo htmlspecialchars($article['image']); ?>" alt=""></div>
                <div class="blog-content">
                    <span class="blog-date"><?php echo htmlspecialchars($article['date']); ?></span>
                    <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                    <p class="blog-excerpt"><?php echo htmlspecialchars($article['description']); ?></p>
                    <a href="#" class="btn btn-outline">Lire l'article</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require_once 'inc/footer.php'; ?>