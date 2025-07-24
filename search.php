<?php require_once 'inc/header.php'; ?>

<main class="section">
    <div class="container">
        <div class="section-title"><h2>Recherche</h2></div>
        
        <div class="search-container">
            
            <?php if (isset($_GET['q'])): 
                $query = strtolower($_GET['q']);
                $products = array_filter(get_products(), function($product) use ($query) {
                    return strpos(strtolower($product['titre']), $query) !== false || 
                           strpos(strtolower($product['description']), $query) !== false;
                });
                
                $services = array_filter(get_services(), function($service) use ($query) {
                    return strpos(strtolower($service['titre']), $query) !== false || 
                           strpos(strtolower($service['description']), $query) !== false;
                });
            ?>
            
            <div class="search-results">
                <?php if (!empty($products)): ?>
                    <h3>Produits correspondants</h3>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" data-id="<?php echo $product['id']; ?>">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?>">
                                </div>
                                <div class="product-info">
                                    <span class="product-category"><?php echo htmlspecialchars($product['categorie']); ?></span>
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['titre']); ?></h3>
                                    <div class="product-price"><?php echo htmlspecialchars($product['prix']); ?>XOF</div>
                                    <div class="product-actions">
                                        <a href="/product.php?id=<?php echo $product['id']; ?>" class="btn">Voir détails</a>
                                        <form action="/cart-action.php" method="post" style="display:inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="action" value="add">
                                            <button type="submit" class="btn">Ajouter</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($services)): ?>
                    <h3>Services correspondants</h3>
                    <div class="services-container">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="service-icon"><i class="fas <?php echo htmlspecialchars($service['icone']); ?>"></i></div>
                                <h3><?php echo htmlspecialchars($service['titre']); ?></h3>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <a href="/services.php" class="btn btn-outline" style="margin-top: 20px;">En savoir plus</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($products) && empty($services)): ?>
                    <p>Aucun résultat trouvé pour "<?php echo htmlspecialchars($_GET['q']); ?>"</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'inc/footer.php'; ?>