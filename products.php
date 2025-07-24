<?php 
    require_once 'inc/header.php';
    require_once 'backend/controllers/ProductController.php';
?>

<!-- Ajoutez ce style dans la section head ou juste avant la section main -->
<style>
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 50px 20px;
        margin: 30px auto;
        background-color: #f9f9f9;
        border-radius: 8px;
        width: 100%;
    }
    
    .empty-state i {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 15px;
    }
    
    .empty-state p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .products-grid .empty-state {
        grid-column: 1 / -1;
    }
</style>

<main class="container section">
    <div class="section-title">
        <h2>
            <?php 
            if (isset($_GET['category'])) {
                echo 'Nos ' . htmlspecialchars($_GET['category']);
            } else {
                echo 'Tous Nos Produits';
            }
            ?>
        </h2>
        <p>Découvrez notre sélection d'articles de décoration pour embellir votre intérieur</p>
        
        <?php if (isset($_GET['category'])): ?>
            <a href="products.php" class="btn" style="margin-top: 10px;">Voir tous les produits</a>
        <?php endif; ?>
    </div>

    <div class="products-grid">
        <?php 
        $productController = new ProductController();
        $tous_les_produits = $productController->getAllProducts();
        $produits_a_afficher = $tous_les_produits;
        
        if (isset($_GET['category'])) {
            $category_filter = $_GET['category'];
            $produits_a_afficher = array_filter($tous_les_produits, function($produit) use ($category_filter) {
                return $produit['category_name'] === $category_filter;
            });
        }
        
        if (empty($produits_a_afficher)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Aucun produit disponible pour le moment.</p>
                <?php if (isset($_GET['category'])): ?>
                    <a href="products.php" class="btn">Voir tous les produits</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($produits_a_afficher as $produit): ?>
                <div class="product-card" data-id="<?= $produit['id'] ?>">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['name']); ?>">
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($produit['category_name']); ?></span>
                        <h3 class="product-title"><?php echo htmlspecialchars($produit['name']); ?></h3>
                        <div class="product-price"><?php echo number_format($produit['price'], 0, ',', ' '); ?> XOF</div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo htmlspecialchars($produit['id']); ?>" class="btn">Voir détails</a>
                            <form action="cart.php" method="post" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $produit['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn add-to-cart-btn">Ajouter</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
    // Gestion de l'ajout au panier depuis la liste des produits
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mise à jour du compteur du panier
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(el => {
                        el.textContent = data.cart_count;
                    });
                    
                    // Feedback visuel
                    const btn = form.querySelector('.add-to-cart-btn');
                    btn.textContent = 'Ajouté !';
                    setTimeout(() => {
                        btn.textContent = 'Ajouter';
                    }, 2000);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    });
</script>

<?php require_once 'inc/footer.php'; ?>