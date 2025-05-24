<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

$conn = getDBConnection();

// Fetch cart items
$cartItems = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Process cart items directly from session
    foreach ($_SESSION['cart'] as $cartItem) {
        if (isset($cartItem['id'])) {
            $quantity = $cartItem['quantity'];
            $subtotal = $cartItem['price'] * $quantity;
            $total += $subtotal;
            
            $cartItems[] = [
                'id' => $cartItem['id'],
                'name' => $cartItem['name'],
                'price' => $cartItem['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'image_url' => $cartItem['image_url'] ?? 'assets/images/default-product.jpg'
            ];
        }
    }
}

// Handle cart actions through AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    // Update quantity
    if (isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        
        if ($productId && $quantity > 0) {
            // Find the item in the cart
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $productId) {
                    $item['quantity'] = $quantity;
                    $response['success'] = true;
                    $response['message'] = 'Quantity updated';
                    break;
                }
            }
        }
    }
    
    // Remove item from cart
    else if (isset($_POST['action']) && $_POST['action'] === 'remove_item') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        
        if ($productId) {
            foreach ($_SESSION['cart'] as $index => $item) {
                if ($item['id'] == $productId) {
                    unset($_SESSION['cart'][$index]);
                    // Reindex the array
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                    $response['success'] = true;
                    $response['message'] = 'Item removed from cart';
                    break;
                }
            }
        }
    }
    
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .cart-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .cart-empty {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cart-empty i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .cart-empty p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .cart-items {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .cart-item-details h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            color: #ff6b00;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            background: #f8f9fa;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .quantity-btn:hover {
            background: #e9ecef;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.3rem;
        }

        .remove-item {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .remove-item:hover {
            color: #c82333;
        }

        .cart-summary {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            border-top: 2px solid #eee;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .checkout-btn, .btn-secondary {
            width: 100%;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background 0.3s ease;
            text-decoration: none;
            text-align: center;
            line-height: 1.5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 3.5rem;
            margin: 0;
        }

        .checkout-btn {
            background: #ff6b00;
            color: white;
            border: none;
            cursor: pointer;
        }

        .checkout-btn:hover {
            background: #ff8533;
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .cart-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            align-items: center;
        }
        
        .cart-actions a {
            flex: 1;
            height: 3.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Roboto', sans-serif;
        }
        
        .toast {
            display: flex;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 350px;
            font-family: 'Roboto', sans-serif;
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }
        
        .toast.success {
            background-color: #28a745;
            border-left: 5px solid #1e7e34;
        }
        
        .toast.error {
            background-color: #dc3545;
            border-left: 5px solid #bd2130;
        }
        
        .toast-icon {
            margin-right: 12px;
            font-size: 20px;
        }
        
        .toast-content {
            flex-grow: 1;
        }
        
        .toast-title {
            font-weight: 500;
            font-size: 1rem;
            margin-bottom: 4px;
            letter-spacing: 0.25px;
        }
        
        .toast-message {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .toast-close:hover {
            opacity: 1;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 1rem;
            }

            .cart-item-image {
                width: 80px;
                height: 80px;
            }

            .quantity-controls {
                grid-column: 2;
            }

            .remove-item {
                grid-column: 2;
                justify-self: end;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .cart-actions a {
                width: 100%;
            }
            
            .checkout-btn {
                margin-top: 0;
            }
            
            .toast-container {
                left: 10px;
                right: 10px;
                bottom: 10px;
            }
            
            .toast {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Your Cart</h1>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <img src="<?php echo $item['image_url']; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="cart-item-image">
                        
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                        </div>

                        <div class="quantity-controls">
                            <button class="quantity-btn decrease" data-id="<?php echo $item['id']; ?>">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="10" data-id="<?php echo $item['id']; ?>">
                            <button class="quantity-btn increase" data-id="<?php echo $item['id']; ?>">+</button>
                        </div>

                        <button class="remove-item" data-id="<?php echo $item['id']; ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>

            <div class="cart-actions">
                <a href="menu.php" class="btn-secondary">Continue Shopping</a>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Toast notification container -->
    <div class="toast-container" id="toastContainer"></div>

    <?php include '../../includes/footer.php'; ?>

    <script>
    // Function to show a toast notification
    function showToast(type, title, message, duration = 3000) {
        const toastContainer = document.getElementById('toastContainer');
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        // Toast content
        let iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${iconClass}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">&times;</button>
        `;
        
        // Append toast to container
        toastContainer.appendChild(toast);
        
        // Trigger animation (small delay for effect)
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Handle close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
        
        // Auto-remove toast after duration
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }
        }, duration);
    }

    // Function to update item quantity
    function updateQuantity(productId, quantity) {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page to update totals
                location.reload();
            } else {
                showToast('error', 'Error', data.message || 'Could not update quantity');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', 'Could not update quantity. Please try again.');
        });
    }

    // Function to remove item from cart
    function removeItem(productId) {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=remove_item&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page to update cart
                location.reload();
            } else {
                showToast('error', 'Error', data.message || 'Could not remove item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', 'Could not remove item. Please try again.');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Quantity increment/decrement
        const decreaseBtns = document.querySelectorAll('.decrease');
        const increaseBtns = document.querySelectorAll('.increase');
        const quantityInputs = document.querySelectorAll('.quantity-input');
        const removeButtons = document.querySelectorAll('.remove-item');

        // Decrease quantity
        decreaseBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
                let value = parseInt(input.value, 10);
                if (value > 1) {
                    value--;
                    input.value = value;
                    updateQuantity(productId, value);
                }
            });
        });

        // Increase quantity
        increaseBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
                let value = parseInt(input.value, 10);
                if (value < 10) {
                    value++;
                    input.value = value;
                    updateQuantity(productId, value);
                }
            });
        });

        // Input change
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.getAttribute('data-id');
                let value = parseInt(this.value, 10);
                
                // Validate quantity
                if (isNaN(value) || value < 1) {
                    value = 1;
                } else if (value > 10) {
                    value = 10;
                }
                
                this.value = value;
                updateQuantity(productId, value);
            });
        });

        // Remove item
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                removeItem(productId);
            });
        });
    });
    </script>
</body>
</html> 