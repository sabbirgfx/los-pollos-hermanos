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
    <title>Order Details - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
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
            <div class="order-details-container">
                <div class="order-header">
                    <h1>Order #<?php echo $orderId; ?></h1>
                    <div class="order-status">
                        <span class="status-badge <?php echo $order['status']; ?>">
                            <?php echo getOrderStatusText($order['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="order-info-grid">
                    <div class="info-card">
                        <h3>Order Information</h3>
                        <div class="info-group">
                            <label>Order Date</label>
                            <p><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Delivery Type</label>
                            <p><?php echo getDeliveryTypeText($order['delivery_type']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Payment Method</label>
                            <p><?php echo getPaymentMethodText($order['payment_method']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Payment Status</label>
                            <p><?php echo ucfirst($order['payment_status']); ?></p>
                        </div>
                    </div>

                    <?php if ($order['delivery_type'] === 'delivery'): ?>
                        <div class="info-card">
                            <h3>Delivery Information</h3>
                            <div class="info-group">
                                <label>Delivery Address</label>
                                <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            </div>
                            <div class="info-group">
                                <label>Estimated Delivery Time</label>
                                <p><?php echo date('F j, Y, g:i a', strtotime($order['estimated_delivery_time'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="info-card">
                        <h3>Customer Information</h3>
                        <div class="info-group">
                            <label>Name</label>
                            <p><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                        </div>
                        <div class="info-group">
                            <label>Phone</label>
                            <p><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="order-items-card">
                    <h3>Order Items</h3>
                    <div class="order-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                    
                                    <?php 
                                    // Parse special instructions for pizza customizations
                                    $customizationInfo = formatPizzaCustomizations($item['special_instructions']);
                                    ?>
                                    
                                    <?php if ($customizationInfo && $customizationInfo['has_customizations']): ?>
                                        <div class="pizza-customizations">
                                            <strong>Pizza Customizations:</strong>
                                            <p class="customization-details"><?php echo htmlspecialchars($customizationInfo['customizations']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($customizationInfo && !empty($customizationInfo['regular_instructions'])): ?>
                                        <p class="special-instructions">
                                            <strong>Special Instructions:</strong> <?php echo htmlspecialchars($customizationInfo['regular_instructions']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="item-price">
                                    <p class="unit-price"><?php echo formatPrice($item['unit_price']); ?> each</p>
                                    <p class="total-price"><?php echo formatPrice($item['unit_price'] * $item['quantity']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-total">
                        <h3>Total Amount</h3>
                        <p class="total-price"><?php echo formatPrice($order['total_amount']); ?></p>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                    <?php if ($order['status'] === 'pending'): ?>
                        <form method="POST" action="cancel_order.php" class="cancel-form">
                            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                            <button type="submit" class="btn btn-secondary" 
                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                Cancel Order
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Los Pollos Hermanos. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .order-details-container {
            max-width: 1000px;
            margin: 2rem auto;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .info-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .info-group {
            margin-bottom: 1rem;
        }

        .info-group label {
            display: block;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .order-items-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .order-items {
            margin: 1.5rem 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .item-details h4 {
            margin-bottom: 0.5rem;
        }

        .special-instructions {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .item-price {
            text-align: right;
        }

        .unit-price {
            color: #666;
            font-size: 0.9rem;
        }

        .total-price {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #eee;
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .cancel-form {
            margin: 0;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .order-item {
                flex-direction: column;
                gap: 0.5rem;
            }

            .item-price {
                text-align: left;
            }

            .order-actions {
                flex-direction: column;
            }

            .order-actions .btn {
                width: 100%;
            }
        }
    </style>
</body>
</html> 