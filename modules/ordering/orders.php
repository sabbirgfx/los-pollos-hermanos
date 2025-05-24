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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .orders-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 2rem;
            font-weight: 600;
            text-align: center;
        }

        .orders-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .order-item {
            padding: 2rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s ease;
        }

        .order-item:hover {
            background-color: #f8f9fa;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            font-family: 'Roboto', sans-serif;
        }

        .order-date {
            color: #666;
            font-family: 'Roboto', sans-serif;
            font-size: 0.95rem;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-group {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .info-group:hover {
            transform: translateY(-2px);
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .order-total {
            color: #ff6b00;
            font-weight: 600;
        }

        .no-orders {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .no-orders p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #555;
        }

        .btn-view-details {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #ff6b00;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-view-details:hover {
            background: #ff8533;
            transform: translateY(-2px);
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #ff6b00;
        }

        .status-processing {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-cancelled {
            background-color: #fbe9e7;
            color: #d32f2f;
        }

        @media (max-width: 768px) {
            .orders-container {
                margin: 1rem;
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .order-item {
                padding: 1.5rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .order-info {
                grid-template-columns: 1fr;
                gap: 1rem;
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
        <div class="orders-container">
            <h1 class="page-title">My Orders</h1>

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
                                    <div class="info-value">
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo getOrderStatusText($order['status']); ?>
                                        </span>
                                    </div>
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

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 