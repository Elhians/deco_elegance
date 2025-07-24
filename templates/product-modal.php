<?php
require_once '../inc/functions.php';

if (isset($_GET['id'])) {
    $product = get_product_by_id($_GET['id']);
    if ($product):
?>
<div class="product-modal">
    <div class="product-modal-images">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?>" id="mainProductImage">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?> vue 1">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?> vue 2">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?> vue 3">
    </div>
    
    <div class="product-modal-info">
        <h1><?php echo htmlspecialchars($product['titre']); ?></h1>
        <div class="product-price"><?php echo htmlspecialchars($product['prix']); ?>XOF</div>
        <div class="product-description">
            <p><?php echo htmlspecialchars($product['description']); ?></p>
        </div>
        
        <form method="post" action="/cart-action.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <?php if (!empty($product['optionsCouleur'])): ?>
            <div class="product-options">
                <label for="productColor">Couleur :</label>
                <select id="productColor" name="color">
                    <?php foreach($product['optionsCouleur'] as $couleur): ?>
                    <option value="<?php echo htmlspecialchars($couleur); ?>"><?php echo htmlspecialchars($couleur); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="quantity-selector">
                <label>Quantité :</label>
                <button type="button" class="quantity-minus">-</button>
                <input type="number" value="1" min="1" name="quantity" class="quantity-input">
                <button type="button" class="quantity-plus">+</button>
            </div>
            
            <button type="submit" class="btn btn-add-to-cart" style="width: 100%;">Ajouter aau panier</button>
        </form>
    </div>
</div>
<?php
    else:
        echo "<p>Produit non trouvé</p>";
    endif;
}
?>