<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Check if user is logged in and has the delivery staff role
if (!isLoggedIn() || !hasRole('delivery_staff')) {
    redirect('../../index.php');
    exit();
}

// Initialize database connection
$conn = getDBConnection();

// Handle actions
$message = '';
$error = '';

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    if ($status === 'out_for_delivery' || $status === 'delivered') {
        try {
            updateOrderStatus($orderId, $status);
            $message = "Order status updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating order status: " . $e->getMessage();
        }
    } else {
        $error = "Invalid status for delivery staff.";
    }
}

// Get filters
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Build query for delivery-relevant orders
$query = "SELECT o.*, u.username, u.first_name, u.last_name, u.phone,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.delivery_type = 'delivery' 
          AND o.status IN ('ready_for_delivery', 'out_for_delivery')";

if (!empty($status_filter)) {
    $query .= " AND o.status = ?";
    $params = [$status_filter];
} else {
    $params = [];
}

$query .= " ORDER BY o.created_at ASC";

// Get orders
try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Orders - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="staff-container">
        <div class="staff-header">
            <h1>Delivery Orders</h1>
            <p>Manage deliveries and update order status</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="status_filter">Filter by Status:</label>
                    <select name="status_filter" id="status_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">All Delivery Orders</option>
                        <option value="ready_for_delivery" <?php echo $status_filter === 'ready_for_delivery' ? 'selected' : ''; ?>>Ready for Delivery</option>
                        <option value="out_for_delivery" <?php echo $status_filter === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="orders-grid">
            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <p>No orders requiring delivery at this time.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo getOrderStatusText($order['status']); ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="detail-row">
                                <span class="detail-label">Customer:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Address:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Items:</span>
                                <span class="detail-value"><?php echo $order['item_count']; ?> items</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Time:</span>
                                <span class="detail-value"><?php echo date('g:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="order-actions">
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Details</a>
                            
                            <?php if ($order['status'] === 'ready_for_delivery'): ?>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="status" value="out_for_delivery">
                                    <button type="submit" name="update_status" class="btn btn-primary">Start Delivery</button>
                                </form>
                            <?php elseif ($order['status'] === 'out_for_delivery'): ?>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" name="update_status" class="btn btn-primary">Mark as Delivered</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Auto-refresh the page every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html> 