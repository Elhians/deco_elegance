<?php
session_start();
require_once 'inc/functions.php';
require_once 'backend/controllers/OrderController.php';

// Vérifier si l'utilisateur est connecté pour passer commande
$isLoggedIn = isset($_SESSION['user_id']);

// Traitement du passage de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!$isLoggedIn) {
        // Rediriger vers la connexion avec un message
        $_SESSION['redirect_after_login'] = 'cart.php';
        $_SESSION['login_message'] = 'Veuillez vous connecter pour finaliser votre commande';
        header('Location: login.php');
        exit;
    }

    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $user_id = $_SESSION['user_id'];

    // Utiliser OrderController pour créer la commande
    $orderController = new OrderController();
    $result = $orderController->createOrderFromCart($user_id, null);
    
    if ($result['success']) {
        // Vider le panier
        $_SESSION['cart'] = [];
        $_SESSION['order_success'] = true;
        $_SESSION['last_order_id'] = $result['order_id'];
        
        // Rediriger vers la page de confirmation
        header('Location: order_confirmation.php');
        exit;
    } else {
        // Garder le message d'erreur pour l'afficher
        $error_message = $result['message'];
    }
}

// Gestion des actions AJAX sur le panier
handle_cart_actions();

$cart_details = get_cart_items_details();
$cart_items = $cart_details['items'];
$cart_total = $cart_details['total'];

require_once 'inc/header.php';
?>

<section class="cart-page">
    <div class="cart-container">
        <div class="cart-header">
            <h1>Mon Panier</h1>
            <p>Vérifiez vos articles avant de passer commande</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Votre panier est vide</h2>
                <p>Parcourez nos collections et trouvez des articles qui vous plaisent</p>
                <a href="products.php" class="btn">Découvrir nos produits</a>
            </div>
        <?php else: ?>
            <form action="cart.php" method="POST">
                <input type="hidden" name="place_order" value="1">
                <div class="cart-content">
                    <div class="cart-items-container">
                        <?php foreach ($cart_items as $index => $item): ?>
                            <div class="cart-item" data-id="<?= intval($item['id']) ?>">
                                <div class="cart-item-image">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                <div class="cart-item-info">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <div class="cart-item-price"><?= number_format($item['price'], 0, ',', ' ') ?> XOF</div>
                                    <div class="cart-item-quantity">
                                        <button type="button" class="quantity-minus">-</button>
                                        <input type="number" name="quantity[]" value="<?= intval($item['quantity']) ?>" min="1" class="quantity-input">
                                        <button type="button" class="quantity-plus">+</button>
                                    </div>
                                    <input type="hidden" name="product_id[]" value="<?= intval($item['id']) ?>">
                                    <button type="button" class="cart-item-remove">Supprimer</button>
                                </div>
                                <div class="cart-item-subtotal">
                                    <div class="price"><?= number_format($item['subtotal'], 0, ',', ' ') ?> XOF</div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <a href="products.php" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i> Continuer vos achats
                        </a>
                    </div>

                    <div class="cart-summary">
                        <h3 class="summary-title">Récapitulatif</h3>

                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Sous-total</span>
                                <span><?= number_format($cart_total, 0, ',', ' ') ?> XOF</span>
                            </div>
                            <div class="summary-row">
                                <span>Livraison</span>
                                <span>À calculer</span>
                            </div>
                        </div>

                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span><?= number_format($cart_total, 0, ',', ' ') ?> XOF</span>
                        </div>

                        <?php if ($isLoggedIn): ?>
                            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Passer la commande</button>
                        <?php else: ?>
                            <a href="login.php?redirect=cart" class="btn" style="display:block; text-align:center; width: 100%; margin-top: 20px;">Connectez-vous pour commander</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<script>
    // Gestion des quantités
    document.querySelectorAll('.quantity-minus').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.nextElementSibling;
            input.value = Math.max(1, parseInt(input.value) - 1);
            updateCartItemQuantity(input);
        });
    });

    document.querySelectorAll('.quantity-plus').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            input.value = parseInt(input.value) + 1;
            updateCartItemQuantity(input);
        });
    });

    // Mise à jour de la quantité d'un article
    function updateCartItemQuantity(input) {
        const cartItem = input.closest('.cart-item');
        const productId = cartItem.getAttribute('data-id');
        const quantity = parseInt(input.value);

        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le sous-total de l'article
                const subtotalElement = cartItem.querySelector('.cart-item-subtotal .price');
                subtotalElement.textContent = data.item_subtotal.toLocaleString('fr-FR') + ' XOF';
                
                // Mettre à jour le total du panier
                updateCartSummary(data.cart_total);
                updateCartCount(data.cart_count);
            }
        });
    }

    // Suppression d'un article
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', function () {
            if (confirm('Voulez-vous vraiment supprimer cet article du panier ?')) {
                const cartItem = this.closest('.cart-item');
                const productId = cartItem.getAttribute('data-id');

                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartItem.remove();
                        updateCartSummary(data.cart_total);
                        updateCartCount(data.cart_count);
                        if (document.querySelectorAll('.cart-item').length === 0) location.reload();
                    }
                });
            }
        });
    });

    // Mise à jour du récapitulatif
    function updateCartSummary(total) {
        document.querySelectorAll('.summary-total span:nth-child(2)').forEach(el => {
            el.textContent = total.toLocaleString('fr-FR') + ' XOF';
        });
        document.querySelector('.summary-details .summary-row:first-child span:nth-child(2)').textContent = 
            total.toLocaleString('fr-FR') + ' XOF';
    }

    // Mise à jour du compteur du panier
    function updateCartCount(count) {
        document.querySelectorAll('.cart-count').forEach(el => {
            el.textContent = count;
        });
    }
</script>

<?php require_once 'inc/footer.php'; ?>
