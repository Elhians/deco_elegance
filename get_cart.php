<?php
require_once 'inc/functions.php';

$cart_data = get_cart_items_details();
$cart_items = $cart_data['items'];

if (!empty($cart_items)): 
    foreach ($cart_items as $item): ?>
        <div class="cart-item" data-id="<?php echo $item['id']; ?>">
            <div class="cart-item-image">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
            </div>
            <div class="cart-item-info">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <div class="cart-item-price"><?php echo number_format($item['price'], 0, ',', ' '); ?> XOF</div>
                <div class="cart-item-quantity">
                    <button type="button" class="quantity-minus">-</button>
                    <input type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" class="quantity-input">
                    <button type="button" class="quantity-plus">+</button>
                </div>
                <span class="cart-item-remove">Supprimer</span>
            </div>
            <div class="cart-item-subtotal"><?php echo number_format($item['subtotal'], 0, ',', ' '); ?> XOF</div>
        </div>
    <?php endforeach; ?>
    <script>
        document.querySelector('.empty-cart-message').style.display = 'none';
        document.querySelector('.cart-total-amount').textContent = '<?php echo number_format($cart_data['total'], 0, ',', ' '); ?> XOF';
    </script>
<?php else: ?>
    <script>
        document.querySelector('.empty-cart-message').style.display = 'block';
        document.querySelector('.cart-total-amount').textContent = '0 XOF';
    </script>
<?php endif; ?>