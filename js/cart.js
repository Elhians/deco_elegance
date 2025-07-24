document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.closest('[data-id]').getAttribute('data-id');
            const quantityInput = this.closest('.product-actions')?.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    document.querySelectorAll('.cart-count').forEach(el => {
                        el.textContent = data.cart_count;
                    });
                    
                    // Show success message
                    alert('Produit ajouté au panier');
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout au panier');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue');
            });
        });
    });
    
    // Cart quantity buttons
    document.querySelectorAll('.quantity-minus, .quantity-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = btn.classList.contains('quantity-minus') ? 
                this.nextElementSibling : this.previousElementSibling;
                
            if (btn.classList.contains('quantity-minus')) {
                input.value = Math.max(1, parseInt(input.value) - 1);
            } else {
                input.value = parseInt(input.value) + 1;
            }
            
            // If we're on the cart page, update the cart
            if (document.querySelector('.cart-page')) {
                updateCartItem(input);
            }
        });
    });
});

function updateCartItem(input) {
    const cartItem = input.closest('.cart-item');
    const productId = cartItem.getAttribute('data-id');
    const quantity = parseInt(input.value);
    
    // Send AJAX request to update
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update subtotal and total
            cartItem.querySelector('.cart-item-subtotal').textContent = 
                data.item_subtotal.toLocaleString('fr-FR') + ' XOF';
                
            document.querySelector('.cart-total-amount').textContent = 
                data.cart_total.toLocaleString('fr-FR') + ' XOF';
        }
    });
}