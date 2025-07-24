<?php
require_once 'inc/functions.php';

$id = $_GET['id'] ?? null;
$produit = get_product_by_id($id);

if ($produit): ?>
    <div class="product-modal-images">
        <img src="<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['titre']); ?>" id="mainProductImage">
        <img src="<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['titre']); ?> vue 1">
        <img src="<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['titre']); ?> vue 2">
        <img src="<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['titre']); ?> vue 3">
    </div>
    
    <div class="product-modal-info">
        <h1><?php echo htmlspecialchars($produit['titre']); ?></h1>
        <div class="product-price"><?php echo htmlspecialchars($produit['prix']); ?>XOF</div>
        <div class="product-description">
            <p><?php echo htmlspecialchars($produit['description']); ?></p>
        </div>
        
        <form method="post" action="cart.php">
            <input type="hidden" name="product_id" value="<?php echo $produit['id']; ?>">
            
            <?php if (!empty($produit['optionsCouleur'])): ?>
            <div class="product-options">
                <label for="productColor">Couleur :</label>
                <select id="productColor" name="color">
                    <?php foreach($produit['optionsCouleur'] as $couleur): ?>
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
            
            <button type="submit" class="btn btn-add-to-cart" style="width: 100%;">Ajouter au panier</button>
        </form>
    </div>
<?php else: ?>
    <p>Produit non trouvé</p>
<?php endif; ?>