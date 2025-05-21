<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Initialize database connection
$conn = getDBConnection();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/modules/auth/login.php');
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    redirect('/modules/ordering/menu.php');
}

// Get user details
$user = getUserDetails($_SESSION['user_id']);

// Calculate order total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deliveryType = sanitizeInput($_POST['delivery_type']);
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    $deliveryAddress = sanitizeInput($_POST['delivery_address']);
    $specialInstructions = sanitizeInput($_POST['special_instructions'] ?? '');

    // Validation
    if ($deliveryType === 'delivery' && empty($deliveryAddress)) {
        $error = 'Please provide a delivery address';
    } else {
        try {
            $conn->beginTransaction();

            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders (user_id, total_amount, status, delivery_type, delivery_address, 
                                  payment_method, payment_status, estimated_delivery_time)
                VALUES (?, ?, 'pending', ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 45 MINUTE))
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $cartTotal,
                $deliveryType,
                $deliveryAddress,
                $paymentMethod
            ]);
            
            $orderId = $conn->lastInsertId();

            // Add order items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price, special_instructions)
                VALUES (?, ?, ?, ?, ?)
            ");

            // Loop through cart items correctly
            foreach ($_SESSION['cart'] as $item) {
                $stmt->execute([
                    $orderId,
                    $item['id'], // This is the product ID
                    $item['quantity'],
                    $item['price'],
                    $specialInstructions
                ]);
            }

            $conn->commit();

            // Clear cart
            $_SESSION['cart'] = [];

            // Redirect to order confirmation
            redirect("/modules/ordering/order_confirmation.php?order_id=$orderId");
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'An error occurred while processing your order: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .order-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .order-summary h2 {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
            font-size: 1.2rem;
        }

        .cart-item {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-details h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .menu-item-price {
            color: #ff6b00;
            font-weight: 600;
        }

        .order-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total h3 {
            font-size: 1.1rem;
        }

        .checkout-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .delivery-options, .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .delivery-options label, .payment-options label {
            display: block;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }

        .delivery-options input[type="radio"], .payment-options input[type="radio"] {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: #ff6b00;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="../../index.php" class="logo">Los Pollos Hermanos</a>
            <div class="nav-links">
                <a href="menu.php">Menu</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="checkout-container">
            <h1>Checkout</h1>

            <?php if ($error): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>

            <div class="checkout-grid">
                <!-- Order Summary -->
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                    <p class="menu-item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        <h3>Total</h3>
                        <div class="menu-item-price"><?php echo formatPrice($cartTotal); ?></div>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="checkout-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Delivery Type</label>
                            <div class="delivery-options">
                                <label>
                                    <input type="radio" name="delivery_type" value="delivery" checked>
                                    Delivery
                                </label>
                                <label>
                                    <input type="radio" name="delivery_type" value="pickup">
                                    Pickup
                                </label>
                            </div>
                        </div>

                        <div class="form-group delivery-address-group">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <textarea id="delivery_address" name="delivery_address" class="form-control" rows="2"
                                      placeholder="Enter your delivery address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <div class="payment-options">
                                <label>
                                    <input type="radio" name="payment_method" value="online" checked>
                                    Online Payment
                                </label>
                                <label>
                                    <input type="radio" name="payment_method" value="cash">
                                    Cash on Delivery
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="special_instructions" class="form-label">Special Instructions (Optional)</label>
                            <textarea id="special_instructions" name="special_instructions" class="form-control" rows="2"
                                      placeholder="Any special instructions for your order?"></textarea>
                        </div>

                        <button type="submit" class="btn-primary">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Los Pollos Hermanos. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Show/hide delivery address based on delivery type
        document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const addressGroup = document.querySelector('.delivery-address-group');
                addressGroup.style.display = this.value === 'delivery' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html> 