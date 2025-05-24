<?php
// Set path variable for header/footer
$isSubDirectory = true;
require_once '../config/database.php';
require_once '../includes/header.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.08;
$total = $subtotal + $tax;

// Get user information if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}
?>

<div class="checkout-container">
    <h1>Checkout</h1>
    
    <div class="checkout-grid">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <?php if (isset($item['size'])): ?>
                                <p class="item-size">Size: <?php echo ucfirst($item['size']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['toppings'])): ?>
                                <p class="item-toppings">Toppings: <?php echo htmlspecialchars(implode(', ', $item['toppings'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="item-quantity">
                            <span>Qty: <?php echo $item['quantity']; ?></span>
                        </div>
                        <div class="item-total">
                            <p>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="price-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (8%):</span>
                    <span>$<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
        
        <form id="checkout-form" class="checkout-form">
            <div class="form-section">
                <h2>Delivery Information</h2>
                <div class="delivery-options">
                    <label class="delivery-option">
                        <input type="radio" name="delivery_type" value="delivery" checked>
                        <span>Delivery</span>
                    </label>
                    <label class="delivery-option">
                        <input type="radio" name="delivery_type" value="pickup">
                        <span>Pickup</span>
                    </label>
                </div>
                
                <div id="delivery-fields">
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" name="address" required><?php echo $user ? htmlspecialchars($user['address']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div id="pickup-fields" style="display: none;">
                    <div class="form-group">
                        <label for="pickup_time">Preferred Pickup Time</label>
                        <input type="datetime-local" id="pickup_time" name="pickup_time" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Contact Information</h2>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $user ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required 
                           value="<?php echo $user ? htmlspecialchars($user['phone']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h2>Payment Method</h2>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="online" checked>
                        <span>Online Payment</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cash">
                        <span>Cash on Delivery/Pickup</span>
                    </label>
                </div>
                
                <div id="online-payment-fields">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" pattern="[0-9]{16}" placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" id="expiry" name="expiry" pattern="(0[1-9]|1[0-2])\/([0-9]{2})" placeholder="MM/YY">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" pattern="[0-9]{3,4}" placeholder="123">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </div>
        </form>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}

.order-summary, .checkout-form {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h2 {
    margin-bottom: 1rem;
    color: #333;
}

.delivery-options, .payment-options {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.delivery-option, .payment-option {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e31837;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.delivery-option input[type="radio"], .payment-option input[type="radio"] {
    display: none;
}

.delivery-option input[type="radio"]:checked + span,
.payment-option input[type="radio"]:checked + span {
    color: white;
}

.delivery-option input[type="radio"]:checked + span::before,
.payment-option input[type="radio"]:checked + span::before {
    background-color: #e31837;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #666;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.cart-items {
    margin-bottom: 1rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

.price-summary {
    background-color: #f9f9f9;
    padding: 1rem;
    border-radius: 5px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #e31837;
    border-top: 1px solid #ddd;
    padding-top: 1rem;
    margin-top: 1rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
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
    const form = document.getElementById('checkout-form');
    const deliveryType = document.querySelectorAll('input[name="delivery_type"]');
    const deliveryFields = document.getElementById('delivery-fields');
    const pickupFields = document.getElementById('pickup-fields');
    const paymentMethod = document.querySelectorAll('input[name="payment_method"]');
    const onlinePaymentFields = document.getElementById('online-payment-fields');
    
    // Handle delivery type change
    deliveryType.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'delivery') {
                deliveryFields.style.display = 'block';
                pickupFields.style.display = 'none';
                document.getElementById('address').required = true;
                document.getElementById('pickup_time').required = false;
            } else {
                deliveryFields.style.display = 'none';
                pickupFields.style.display = 'block';
                document.getElementById('address').required = false;
                document.getElementById('pickup_time').required = true;
            }
        });
    });
    
    // Handle payment method change
    paymentMethod.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'online') {
                onlinePaymentFields.style.display = 'block';
                document.getElementById('card_number').required = true;
                document.getElementById('expiry').required = true;
                document.getElementById('cvv').required = true;
            } else {
                onlinePaymentFields.style.display = 'none';
                document.getElementById('card_number').required = false;
                document.getElementById('expiry').required = false;
                document.getElementById('cvv').required = false;
            }
        });
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const orderData = {
            delivery_type: formData.get('delivery_type'),
            payment_method: formData.get('payment_method'),
            name: formData.get('name'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            address: formData.get('address'),
            pickup_time: formData.get('pickup_time'),
            card_number: formData.get('card_number'),
            expiry: formData.get('expiry'),
            cvv: formData.get('cvv')
        };
        
        // Submit order
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'order_confirmation.php?order_id=' + data.order_id;
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 