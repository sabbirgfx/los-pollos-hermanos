<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/modules/auth/login.php');
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('/modules/ordering/orders.php');
}

$orderId = (int)$_GET['order_id'];

// Get order details
$order = getOrderDetails($orderId);

// Check if order exists and belongs to the user
if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
    redirect('/modules/ordering/orders.php');
}

// Get order items
$orderItems = getOrderItems($orderId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Los Pollos Hermanos</title>
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
            <div class="order-confirmation">
                <div class="success-message">
                    <h1 class="text-center mb-2">Order Confirmed!</h1>
                    <p class="text-center">Thank you for your order. Your order number is #<?php echo $orderId; ?></p>
                </div>

                <div class="order-details">
                    <h2>Order Details</h2>
                    
                    <div class="order-info">
                        <div class="info-group">
                            <h3>Order Status</h3>
                            <p><?php echo getOrderStatusText($order['status']); ?></p>
                        </div>

                        <div class="info-group">
                            <h3>Delivery Type</h3>
                            <p><?php echo getDeliveryTypeText($order['delivery_type']); ?></p>
                        </div>

                        <?php if ($order['delivery_type'] === 'delivery'): ?>
                            <div class="info-group">
                                <h3>Delivery Address</h3>
                                <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="info-group">
                            <h3>Payment Method</h3>
                            <p><?php echo getPaymentMethodText($order['payment_method']); ?></p>
                        </div>

                        <div class="info-group">
                            <h3>Estimated Delivery Time</h3>
                            <p><?php echo date('F j, Y, g:i a', strtotime($order['estimated_delivery_time'])); ?></p>
                        </div>
                    </div>

                    <div class="order-items">
                        <h3>Order Items</h3>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                    <?php if ($item['special_instructions']): ?>
                                        <p class="special-instructions">
                                            Special Instructions: <?php echo htmlspecialchars($item['special_instructions']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="item-price">
                                    <?php echo formatPrice($item['unit_price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="order-total">
                            <h3>Total Amount</h3>
                            <p class="total-price"><?php echo formatPrice($order['total_amount']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    <a href="menu.php" class="btn btn-secondary">Order Again</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Los Pollos Hermanos. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 