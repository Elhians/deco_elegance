// Mobile menu toggle
document.getElementById('mobileMenuBtn').addEventListener('click', () => {
    const mainNav = document.getElementById('mainNav');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    
    mainNav.classList.toggle('active');
    mobileMenuBtn.innerHTML = mainNav.classList.contains('active') ? 
        '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
});

// Product modal
document.querySelectorAll('.btn-view').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const productId = e.target.closest('.product-card').getAttribute('data-id');
        
        try {
            const response = await fetch(`get_product.php?id=${productId}`);
            const productHtml = await response.text();
            
            document.getElementById('productModalContent').innerHTML = productHtml;
            document.getElementById('productModal').classList.add('active');
            
            // Initialize product modal interactions
            initProductModal();
        } catch (error) {
            console.error('Error loading product:', error);
        }
    });
});

// Add to cart
document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const productId = e.target.closest('.product-card').getAttribute('data-id');
        
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', 'add');
            formData.append('quantity', 1);
            
            await fetch('cart.php', {
                method: 'POST',
                body: formData
            });
            
            // Update cart count
            const cartCount = document.querySelector('.cart-count');
            cartCount.textContent = parseInt(cartCount.textContent) + 1;
            
            // Show feedback
            btn.textContent = 'Ajouté !';
            setTimeout(() => {
                btn.textContent = 'Ajouter';
            }, 2000);
        } catch (error) {
            console.error('Error adding to cart:', error);
        }
    });
});

// Cart modal
document.getElementById('cartBtn').addEventListener('click', async (e) => {
    e.preventDefault();
    
    try {
        const response = await fetch('get_cart.php');
        const cartHtml = await response.text();
        
        document.querySelector('.cart-items').innerHTML = cartHtml;
        document.getElementById('cartModal').classList.add('active');
        
        // Initialize cart interactions
        initCartInteractions();
    } catch (error) {
        console.error('Error loading cart:', error);
    }
});

// Close modals
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    });
});

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Newsletter form
document.getElementById('newsletterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = e.target.querySelector('input').value;
    
    try {
        const formData = new FormData();
        formData.append('email', email);
        
        await fetch('newsletter.php', {
            method: 'POST',
            body: formData
        });
        
        alert('Merci pour votre inscription à notre newsletter !');
        e.target.reset();
    } catch (error) {
        console.error('Error subscribing:', error);
        alert('Une erreur est survenue. Veuillez réessayer.');
    }
});

// Initialize product modal interactions
function initProductModal() {
    // Product image gallery in modal
    const productThumbnails = document.querySelectorAll('.product-modal-images img');
    const mainProductImage = document.getElementById('mainProductImage');
    
    if (productThumbnails && mainProductImage) {
        productThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                mainProductImage.src = thumbnail.src;
            });
        });
    }
    
    // Add to cart in modal
    const modalAddToCart = document.querySelector('.product-modal .btn-add-to-cart');
    if (modalAddToCart) {
        modalAddToCart.addEventListener('click', async (e) => {
            e.preventDefault();
            const form = e.target.closest('form');
            const formData = new FormData(form);
            formData.append('action', 'add');
            
            try {
                await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                cartCount.textContent = parseInt(cartCount.textContent) + parseInt(formData.get('quantity') || 1);
                
                // Show feedback
                e.target.textContent = 'Ajouté !';
                setTimeout(() => {
                    e.target.textContent = 'Ajouter au panier';
                }, 2000);
            } catch (error) {
                console.error('Error adding to cart:', error);
            }
        });
    }
}

// Initialize cart interactions
function initCartInteractions() {
    // Quantity changes
    document.querySelectorAll('.cart-item-quantity button').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const input = e.target.closest('.cart-item-quantity').querySelector('input');
            const productId = e.target.closest('.cart-item').getAttribute('data-id');
            
            if (e.target.classList.contains('quantity-minus')) {
                input.value = Math.max(1, parseInt(input.value) - 1);
            } else {
                input.value = parseInt(input.value) + 1;
            }
            
            await updateCartItem(productId, input.value);
        });
    });
    
    // Direct input changes
    document.querySelectorAll('.cart-item-quantity input').forEach(input => {
        input.addEventListener('change', async (e) => {
            const productId = e.target.closest('.cart-item').getAttribute('data-id');
            const quantity = Math.max(1, parseInt(e.target.value) || 1);
            
            await updateCartItem(productId, quantity);
        });
    });
    
    // Remove items
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const productId = e.target.closest('.cart-item').getAttribute('data-id');
            
            try {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', 'remove');
                
                await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Reload cart
                const response = await fetch('get_cart.php');
                const cartHtml = await response.text();
                document.querySelector('.cart-items').innerHTML = cartHtml;
                
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                const count = document.querySelectorAll('.cart-item').length;
                cartCount.textContent = count;
                
                if (count === 0) {
                    document.querySelector('.empty-cart-message').style.display = 'block';
                }
                
                initCartInteractions();
            } catch (error) {
                console.error('Error removing item:', error);
            }
        });
    });
}

// Helper function to update cart item
async function updateCartItem(productId, quantity) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'update');
        formData.append('quantity', quantity);
        
        await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        // Reload cart
        const response = await fetch('get_cart.php');
        const cartHtml = await response.text();
        document.querySelector('.cart-items').innerHTML = cartHtml;
        
        initCartInteractions();
    } catch (error) {
        console.error('Error updating cart:', error);
    }
}


// Gestion du panier
document.addEventListener('DOMContentLoaded', () => {
    // Ajout au panier depuis la page produit
    document.querySelectorAll('.add-to-cart, .add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const form = e.target.closest('form') || e.target.closest('.product-card');
            const productId = form.querySelector('input[name="product_id"]')?.value || form.getAttribute('data-id');
            const quantity = form.querySelector('input[name="quantity"]')?.value || 1;
            
            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&product_id=${productId}&quantity=${quantity}`
                });
                
                const data = await response.json();
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showFeedback('Produit ajouté au panier', e.target);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
    
    // Gestion de la quantité dans le panier
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const input = e.target.parentElement.querySelector('.quantity-input');
            const productId = e.target.closest('.cart-item').getAttribute('data-id');
            let quantity = parseInt(input.value);
            
            if (e.target.classList.contains('minus')) {
                quantity = Math.max(1, quantity - 1);
            } else {
                quantity += 1;
            }
            
            input.value = quantity;
            await updateCartItem(productId, quantity);
        });
    });
    
    // Changement direct de quantité
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', async (e) => {
            const productId = e.target.closest('.cart-item').getAttribute('data-id');
            const quantity = Math.max(1, parseInt(e.target.value) || 1);
            await updateCartItem(productId, quantity);
        });
    });
    
    // Suppression d'un article
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const productId = e.target.closest('.cart-item').getAttribute('data-id');
            
            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                });
                
                const data = await response.json();
                if (data.success) {
                    e.target.closest('.cart-item').remove();
                    updateCartSummary(data.cart_total);
                    updateCartCount(data.cart_count);
                    
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        location.reload();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
    
    // Vider le panier
    document.querySelector('.clear-cart')?.addEventListener('click', async () => {
        try {
            const response = await fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear'
            });
            
            const data = await response.json();
            if (data.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

// Fonction pour mettre à jour un article du panier
async function updateCartItem(productId, quantity) {
    try {
        const response = await fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        });
        
        const data = await response.json();
        if (data.success) {
            updateCartSummary(data.cart_total);
            updateCartCount(data.cart_count);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Mettre à jour le récapitulatif du panier
function updateCartSummary(total) {
    document.querySelectorAll('.subtotal, .cart-total').forEach(el => {
        el.textContent = `${total} XOF`;
    });
}

// Mettre à jour le compteur du panier
function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) cartCount.textContent = count;
}

// Afficher un feedback visuel
function showFeedback(message, element) {
    const originalText = element.textContent;
    element.textContent = message;
    element.classList.add('feedback');
    
    setTimeout(() => {
        element.textContent = originalText;
        element.classList.remove('feedback');
    }, 2000);
}