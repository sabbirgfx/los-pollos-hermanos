<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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
    $specialInstructions = sanitizeInput($_POST['special_instructions']);

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

            foreach ($_SESSION['cart'] as $productId => $item) {
                $stmt->execute([
                    $orderId,
                    $productId,
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
            $error = 'An error occurred while processing your order. Please try again.';
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
        <div class="container">
            <div class="checkout-container">
                <h1 class="text-center mb-2">Checkout</h1>

                <?php if ($error): ?>
                    <?php echo displayError($error); ?>
                <?php endif; ?>

                <div class="checkout-grid">
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <div class="cart-items">
                            <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
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
                            <h3>Total: <?php echo formatPrice($cartTotal); ?></h3>
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
                                <textarea id="delivery_address" name="delivery_address" class="form-control" rows="3"
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
                                <label for="special_instructions" class="form-label">Special Instructions</label>
                                <textarea id="special_instructions" name="special_instructions" class="form-control" rows="3"
                                          placeholder="Any special instructions for your order?"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%;">Place Order</button>
                        </form>
                    </div>
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