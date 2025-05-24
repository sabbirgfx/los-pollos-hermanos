<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
    exit();
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('orders.php');
    exit();
}

$orderId = (int)$_GET['order_id'];
$error = '';
$order = null;
$orderItems = [];

// Initialize database connection
$conn = getDBConnection();

try {
    // Get order details
    $stmt = $conn->prepare("
        SELECT o.*, u.first_name, u.last_name, u.phone, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        redirect('orders.php');
        exit();
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate estimated delivery time
    $estimatedTime = strtotime($order['estimated_delivery_time']);
    $currentTime = time();
    $timeRemaining = $estimatedTime - $currentTime;
    
    // Format time remaining
    if ($timeRemaining > 0) {
        $minutesRemaining = ceil($timeRemaining / 60);
        $timeRemainingText = $minutesRemaining . ' minute' . ($minutesRemaining != 1 ? 's' : '') . ' remaining';
    } else {
        $timeRemainingText = 'Order should be delivered soon';
    }
    
} catch (PDOException $e) {
    $error = "Error fetching order details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .tracking-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .tracking-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .tracking-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .tracking-status {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .status-timeline {
            position: relative;
            padding: 2rem 0;
        }
        
        .status-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: #ddd;
        }
        
        .status-step {
            position: relative;
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .status-step:last-child {
            margin-bottom: 0;
        }
        
        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }
        
        .status-icon.active {
            background: #ff6b00;
            border-color: #ff6b00;
            color: #fff;
        }
        
        .status-icon.completed {
            background: #28a745;
            border-color: #28a745;
            color: #fff;
        }
        
        .status-content {
            position: absolute;
            left: 50%;
            transform: translateX(50%);
            width: 200px;
            text-align: center;
            margin-top: 50px;
        }
        
        .status-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .status-time {
            font-size: 0.9rem;
            color: #666;
        }
        
        .estimated-time {
            text-align: center;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .estimated-time h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .time-remaining {
            font-size: 1.2rem;
            color: #ff6b00;
            font-weight: 600;
        }
        
        .order-details {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .order-items {
            margin-top: 1rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: 600;
            color: #ff6b00;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main>
        <div class="tracking-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php else: ?>
                <div class="tracking-header">
                    <h1>Track Your Order</h1>
                    <p class="tracking-header p">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                </div>
                
                <div class="tracking-status">
                    <div class="status-timeline">
                        <?php
                        $statuses = [
                            'pending' => ['icon' => 'fa-clock', 'title' => 'Order Placed'],
                            'preparing' => ['icon' => 'fa-utensils', 'title' => 'Preparing'],
                            'ready_for_delivery' => ['icon' => 'fa-box', 'title' => 'Ready for Delivery'],
                            'out_for_delivery' => ['icon' => 'fa-truck', 'title' => 'Out for Delivery'],
                            'delivered' => ['icon' => 'fa-check-circle', 'title' => 'Delivered']
                        ];
                        
                        $currentStatus = $order['status'];
                        $statusFound = false;
                        
                        foreach ($statuses as $status => $info):
                            $isActive = $status === $currentStatus;
                            $isCompleted = array_search($status, array_keys($statuses)) < array_search($currentStatus, array_keys($statuses));
                            $statusFound = $statusFound || $isActive;
                        ?>
                            <div class="status-step">
                                <div class="status-icon <?php echo $isActive ? 'active' : ($isCompleted ? 'completed' : ''); ?>">
                                    <i class="fas <?php echo $info['icon']; ?>"></i>
                                </div>
                                <div class="status-content">
                                    <div class="status-title"><?php echo $info['title']; ?></div>
                                    <?php if ($isActive): ?>
                                        <div class="status-time">In Progress</div>
                                    <?php elseif ($isCompleted): ?>
                                        <div class="status-time">Completed</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="estimated-time">
                        <h3>Estimated <?php echo $order['delivery_type'] === 'delivery' ? 'Delivery' : 'Pickup'; ?> Time</h3>
                        <div class="time-remaining"><?php echo $timeRemainingText; ?></div>
                    </div>
                </div>
                
                <div class="order-details">
                    <h2>Order Details</h2>
                    <div class="order-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo $item['image_url'] ?: 'https://via.placeholder.com/80x80?text=Pizza'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="item-image">
                                
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                </div>
                                
                                <div class="item-price">
                                    <?php echo formatPrice($item['unit_price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
    // Auto-refresh the page every minute to update order status
    setTimeout(function() {
        window.location.reload();
    }, 60000);
    </script>
</body>
</html> 