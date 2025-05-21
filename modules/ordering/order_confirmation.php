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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .order-confirmation {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .success-message {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: #f8fff8;
            border-radius: 10px;
            border: 1px solid #4CAF50;
        }

        .success-message h1 {
            color: #2E7D32;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .success-message p {
            color: #4CAF50;
            font-size: 1.1rem;
        }

        .order-details {
            padding: 1.5rem;
            background: #f9f9f9;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .order-details h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-group {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .info-group h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .info-group p {
            color: #333;
            font-size: 1rem;
            margin: 0;
        }

        .order-items {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-details h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .special-instructions {
            color: #666;
            font-style: italic;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .item-price {
            color: #ff6b00;
            font-weight: 600;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .total-price {
            color: #ff6b00;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 0.8rem;
            text-align: center;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
        }

        .btn-primary {
            background: #ff6b00;
            color: white;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        @media (max-width: 768px) {
            .order-confirmation {
                margin: 1rem;
                padding: 1rem;
            }

            .order-info {
                grid-template-columns: 1fr;
            }

            .order-actions {
                flex-direction: column;
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
        <div class="container">
            <div class="order-confirmation">
                <div class="success-message">
                    <h1>Order Confirmed!</h1>
                    <p>Thank you for your order. Your order number is <strong>#<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></strong></p>
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
                            <h3>Estimated <?php echo $order['delivery_type'] === 'delivery' ? 'Delivery' : 'Pickup'; ?> Time</h3>
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
                                            <?php echo htmlspecialchars($item['special_instructions']); ?>
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