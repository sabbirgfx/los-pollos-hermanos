<?php
require_once '../config/database.php';
require_once '../includes/header.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>

<div class="cart-container">
    <h1>Your Cart</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <p>Your cart is empty</p>
            <a href="menu.php" class="btn btn-primary">Browse Menu</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php 
            $total = 0;
            foreach ($_SESSION['cart'] as $index => $item): 
                $total += $item['price'] * $item['quantity'];
            ?>
                <div class="cart-item" data-index="<?php echo $index; ?>">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <?php if (isset($item['size'])): ?>
                            <p class="item-size">Size: <?php echo ucfirst($item['size']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['toppings'])): ?>
                            <p class="item-toppings">Toppings: <?php echo htmlspecialchars(implode(', ', $item['toppings'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['instructions'])): ?>
                            <p class="item-instructions">Special Instructions: <?php echo htmlspecialchars($item['instructions']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="item-price">
                        <p>$<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <div class="item-quantity">
                        <button class="quantity-btn minus">-</button>
                        <input type="number" value="<?php echo $item['quantity']; ?>" min="1" max="10" class="quantity-input">
                        <button class="quantity-btn plus">+</button>
                    </div>
                    <div class="item-total">
                        <p>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                    <button class="remove-item">×</button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (8%):</span>
                <span>$<?php echo number_format($total * 0.08, 2); ?></span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>$<?php echo number_format($total * 1.08, 2); ?></span>
            </div>
        </div>

        <div class="cart-actions">
            <a href="menu.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<style>
.cart-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cart-items {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-details h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.item-size, .item-toppings, .item-instructions {
    color: #666;
    font-size: 0.9rem;
    margin: 0.25rem 0;
}

.item-price, .item-total {
    font-weight: bold;
    color: #e31837;
}

.item-quantity {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 0.25rem;
}

.remove-item {
    background: none;
    border: none;
    color: #999;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

.remove-item:hover {
    color: #e31837;
}

.cart-summary {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #e31837;
    border-top: 1px solid #eee;
    padding-top: 1rem;
    margin-top: 1rem;
}

.cart-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #e31837;
    color: white;
    border: none;
}

.btn-secondary {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn:hover {
    opacity: 0.9;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItems = document.querySelectorAll('.cart-item');
    
    cartItems.forEach(item => {
        const index = item.dataset.index;
        const quantityInput = item.querySelector('.quantity-input');
        const minusBtn = item.querySelector('.minus');
        const plusBtn = item.querySelector('.plus');
        const removeBtn = item.querySelector('.remove-item');
        
        // Update quantity
        function updateQuantity(newQuantity) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    index: index,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the item total
                    const price = parseFloat(item.querySelector('.item-price').textContent.replace('$', ''));
                    item.querySelector('.item-total').textContent = `$${(price * newQuantity).toFixed(2)}`;
                    
                    // Update cart totals
                    updateCartTotals();
                }
            });
        }
        
        // Quantity buttons
        minusBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
                updateQuantity(currentValue - 1);
            }
        });
        
        plusBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < 10) {
                quantityInput.value = currentValue + 1;
                updateQuantity(currentValue + 1);
            }
        });
        
        quantityInput.addEventListener('change', () => {
            let value = parseInt(quantityInput.value);
            if (value < 1) value = 1;
            if (value > 10) value = 10;
            quantityInput.value = value;
            updateQuantity(value);
        });
        
        // Remove item
        removeBtn.addEventListener('click', () => {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    index: index,
                    remove: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                    updateCartTotals();
                    
                    // If cart is empty, reload page to show empty cart message
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        location.reload();
                    }
                }
            });
        });
    });
    
    function updateCartTotals() {
        // This function will be implemented to update the cart summary
        // when items are modified or removed
        location.reload(); // Temporary solution - reload page to update totals
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 