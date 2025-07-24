<?php 
require_once 'inc/header.php'; 
$id = $_GET['id'] ?? null;
$produit = get_product_by_id($id);
?>

<main class="container section">
    <?php if ($produit): ?>
        <div class="product-modal-info" style="max-width: 800px; margin: auto;">
            <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['titre']) ?>" style="width:100%; border-radius: 8px; margin-bottom: 20px;">
            <h1><?= htmlspecialchars($produit['titre']) ?></h1>
            <div class="product-price"><?= number_format($produit['prix'], 0, ',', ' ') ?> XOF</div>
            <div class="product-description">
                <p><?= htmlspecialchars($produit['description']) ?></p>
            </div>
            
            <form id="addToCartForm" method="post" style="margin-top: 30px;">
                <input type="hidden" name="product_id" value="<?= $produit['id'] ?>">
                <input type="hidden" name="action" value="add">

                <?php if (!empty($produit['optionsCouleur'])): ?>
                    <div class="product-options">
                        <label for="productColor">Couleur :</label>
                        <select id="productColor" name="color">
                            <?php foreach($produit['optionsCouleur'] as $couleur): ?>
                                <option value="<?= htmlspecialchars($couleur) ?>"><?= htmlspecialchars($couleur) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="quantity-selector" style="margin-bottom:20px;">
                    <label>Quantité :</label>
                    <input type="number" value="1" min="1" name="quantity" class="quantity-input">
                </div>
                
                <button type="submit" class="btn" id="addToCartBtn" style="width: 100%;">Ajouter au panier</button>
            </form>
        </div>
    <?php else: ?>
        <div class="section-title">
            <h2>Produit non trouvé</h2>
            <p>Le produit que vous cherchez n'existe pas ou plus.</p>
            <a href="products.php" class="btn" style="margin-top:20px;">Retour aux produits</a>
        </div>
    <?php endif; ?>
</main>

<script>
    document.getElementById('addToCartForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const btn = document.getElementById('addToCartBtn');

        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Mise à jour du compteur
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.cart_count;
                });

                // Feedback bouton
                btn.textContent = 'Ajouté !';
                btn.disabled = true;
                setTimeout(() => {
                    btn.textContent = 'Ajouter au panier';
                    btn.disabled = false;
                }, 2000);
            }
        } catch (error) {
            console.error('Erreur lors de l\'ajout au panier :', error);
        }
    });
</script>

<?php require_once 'inc/footer.php'; ?>
