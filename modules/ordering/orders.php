<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/modules/auth/login.php');
}

// Initialize database connection
$conn = getDBConnection();

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.id DESC
");

$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .orders-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
        }

        .orders-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .order-item {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-number {
            font-weight: 600;
            color: #333;
        }

        .order-date {
            color: #666;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-group {
            margin-bottom: 0.5rem;
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .order-total {
            color: #ff6b00;
            font-weight: 600;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .btn-view-details {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #ff6b00;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .btn-view-details:hover {
            background: #ff8533;
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
        <div class="orders-container">
            <h1>My Orders</h1>

            <?php if (empty($orders)): ?>
                <div class="orders-list">
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="menu.php" class="btn-view-details">Browse Menu</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-number">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                <span class="order-date"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="order-info">
                                <div class="info-group">
                                    <div class="info-label">Status</div>
                                    <div class="info-value"><?php echo getOrderStatusText($order['status']); ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Items</div>
                                    <div class="info-value"><?php echo $order['total_items']; ?> items</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Total</div>
                                    <div class="info-value order-total"><?php echo formatPrice($order['total_amount']); ?></div>
                                </div>
                            </div>
                            <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" class="btn-view-details">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Los Pollos Hermanos. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 