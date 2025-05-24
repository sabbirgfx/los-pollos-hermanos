<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Check if user is logged in and has kitchen staff role
if (!isLoggedIn() || !hasRole('kitchen_staff')) {
    redirect('../../index.php');
    exit();
}

// Initialize database connection
$conn = getDBConnection();

// Get order ID
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    redirect('modules/kitchen/orders.php');
    exit();
}

// Handle actions
$message = '';
$error = '';

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $status = $_POST['status'];
    
    if ($status === 'preparing' || $status === 'ready_for_delivery' || $status === 'ready_for_pickup') {
        try {
            updateOrderStatus($orderId, $status);
            $message = "Order status updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating order status: " . $e->getMessage();
        }
    } else {
        $error = "Invalid status for kitchen staff.";
    }
}

// Get order details
try {
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.first_name, u.last_name, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.status IN ('pending', 'preparing', 'ready_for_delivery', 'ready_for_pickup')
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        redirect('modules/kitchen/orders.php');
        exit();
    }

    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.description as product_description
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error fetching order details: " . $e->getMessage();
    $order = null;
    $orderItems = [];
}
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
    <?php include '../../includes/header.php'; ?>
    
    <div class="staff-container">
        <div class="staff-header">
            <h1>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
            <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="order-details-grid">
            <div class="order-info-card">
                <h3>Order Information</h3>
                <div class="info-content">
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo getOrderStatusText($order['status']); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Type:</span>
                        <span class="info-value"><?php echo ucfirst($order['delivery_type']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Time:</span>
                        <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Customer:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                    </div>
                    <?php if ($order['delivery_type'] === 'delivery'): ?>
                        <div class="info-row">
                            <span class="info-label">Delivery Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($order['status'] === 'pending' || $order['status'] === 'preparing'): ?>
                    <div class="order-actions">
                        <form method="POST" class="status-form">
                            <?php if ($order['status'] === 'pending'): ?>
                                <input type="hidden" name="status" value="preparing">
                                <button type="submit" name="update_status" class="btn btn-primary">Start Preparing</button>
                            <?php elseif ($order['status'] === 'preparing'): ?>
                                <input type="hidden" name="status" value="<?php echo $order['delivery_type'] === 'delivery' ? 'ready_for_delivery' : 'ready_for_pickup'; ?>">
                                <button type="submit" name="update_status" class="btn btn-primary">Mark as Ready</button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="order-items-card">
                <h3>Order Items</h3>
                <div class="items-list">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="item-card">
                            <div class="item-header">
                                <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                            </div>
                            <?php if ($item['product_description']): ?>
                                <p class="item-description"><?php echo htmlspecialchars($item['product_description']); ?></p>
                            <?php endif; ?>
                            <?php if ($item['special_instructions']): ?>
                                <div class="special-instructions">
                                    <strong>Special Instructions:</strong>
                                    <p><?php echo htmlspecialchars($item['special_instructions']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 