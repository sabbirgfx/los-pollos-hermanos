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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .order-confirmation {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .success-message p {
            color: #4CAF50;
            font-size: 1.2rem;
            font-family: 'Roboto', sans-serif;
        }

        .order-details {
            padding: 1.5rem;
            background: #f9f9f9;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .order-details h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-group {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .info-group h3 {
            color: #666;
            font-size: 1rem;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
        }

        .info-group p {
            color: #333;
            font-size: 1.1rem;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .order-items {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
            font-size: 1.1rem;
            font-weight: 500;
        }

        .special-instructions {
            color: #666;
            font-style: italic;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            font-family: 'Roboto', sans-serif;
        }

        .item-price {
            color: #ff6b00;
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

        .total-label {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .total-price {
            color: #ff6b00;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .order-actions {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background: #ff6b00;
            color: white;
        }

        .btn-primary:hover {
            background: #ff8533;
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e9e9e9;
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

            .success-message h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php 
    $isSubDirectory = true;
    include '../../includes/header.php'; 
    ?>

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
                            <span class="total-label">Total Amount:</span>
                            <span class="total-price"><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    <a href="menu.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 