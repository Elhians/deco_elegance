<?php require_once 'inc/header.php'; ?>

<main class="section">
    <div class="container">
        <div class="section-title"><h2>Votre Panier</h2></div>

        <?php if (empty($_SESSION['cart'])): ?>
            <p style="text-align:center;">Votre panier est vide.</p>
            <div style="text-align:center; margin-top:20px;">
                <a href="/products.php" class="btn">Voir les produits</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $product_id => $quantity): 
                    $product = get_product_by_id($product_id);
                    if ($product): ?>
                    <div class="cart-item" data-id="<?php echo $product_id; ?>">
                        <div class="cart-item-image">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?>">
                        </div>
                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($product['titre']); ?></h3>
                            <div class="cart-item-price"><?php echo htmlspecialchars($product['prix']); ?>XOF</div>
                            <form action="/cart-action.php" method="post" class="cart-item-quantity">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" class="quantity-input">
                                <button type="submit" class="btn-outline">Mettre à jour</button>
                            </form>
                        </div>
                        <div><?php echo $product['prix'] * $quantity; ?>XOF</div>
                        <a href="/cart-action.php?action=remove&product_id=<?php echo $product_id; ?>" class="cart-item-remove">Supprimer</a>
                    </div>
                <?php endif; endforeach; ?>
            </div>
            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total :</span>
                    <span><?php echo get_cart_total(); ?>XOF</span>
                </div>
                <a href="/checkout.php" class="btn" style="width: 100%;">Passer la commande</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'inc/footer.php'; ?>