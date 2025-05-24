<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('modules/auth/login.php');
    exit();
}

// Initialize database connection
$conn = getDBConnection();

// Get order ID
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    redirect('modules/admin/orders.php');
    exit();
}

// Handle actions
$message = '';
$error = '';

// Handle status update
if (isset($_POST['update_status']) && !empty($_POST['status'])) {
    $status = $_POST['status'];
    
    $validStatuses = ['pending', 'preparing', 'ready_for_delivery', 'out_for_delivery', 
                     'delivered', 'ready_for_pickup', 'picked_up', 'cancelled'];
    
    if (!in_array($status, $validStatuses)) {
        $error = "Invalid status.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            
            $message = "Order status updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating order status: " . $e->getMessage();
        }
    }
}

// Get order details
try {
    $stmt = $conn->prepare("
        SELECT o.*, 
               u.username, u.first_name, u.last_name, u.email, u.phone, u.address as user_address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        redirect('modules/admin/orders.php');
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
    
    // Get customizations for each item
    foreach ($orderItems as $key => $item) {
        $stmt = $conn->prepare("
            SELECT oii.*, i.name as ingredient_name, i.price as ingredient_price
            FROM order_item_ingredients oii
            JOIN ingredients i ON oii.ingredient_id = i.id
            WHERE oii.order_item_id = ?
        ");
        $stmt->execute([$item['id']]);
        $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $orderItems[$key]['customizations'] = $customizations;
    }
    
} catch (PDOException $e) {
    $error = "Error fetching order: " . $e->getMessage();
    $order = null;
    $orderItems = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $orderId; ?> - Los Pollos Hermanos Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <?php include 'includes/admin_sidebar.php'; ?>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Order Details</h1>
                <p>
                    <a href="orders.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($order): ?>
                <div class="order-detail-grid">
                    <div class="order-overview card">
                        <div class="card-header">
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo getOrderStatusText($order['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <div class="detail-label">Customer:</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> 
                                    (<?php echo htmlspecialchars($order['username']); ?>)
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Contact:</div>
                                <div class="detail-value">
                                    <div><?php echo htmlspecialchars($order['email']); ?></div>
                                    <div><?php echo htmlspecialchars($order['phone']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Order Date:</div>
                                <div class="detail-value"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Delivery Type:</div>
                                <div class="detail-value"><?php echo getDeliveryTypeText($order['delivery_type']); ?></div>
                            </div>
                            
                            <?php if ($order['delivery_type'] === 'delivery'): ?>
                                <div class="detail-row">
                                    <div class="detail-label">Delivery Address:</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="detail-row">
                                <div class="detail-label">Payment Method:</div>
                                <div class="detail-value"><?php echo getPaymentMethodText($order['payment_method']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Payment Status:</div>
                                <div class="detail-value">
                                    <?php if ($order['payment_status'] === 'completed'): ?>
                                        <span class="status-badge status-ready_for_delivery">Completed</span>
                                    <?php elseif ($order['payment_status'] === 'pending'): ?>
                                        <span class="status-badge status-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="status-badge status-cancelled">Failed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Total Amount:</div>
                                <div class="detail-value detail-total"><?php echo formatPrice($order['total_amount']); ?></div>
                            </div>
                            
                            <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'picked_up' && $order['status'] !== 'cancelled'): ?>
                                <div class="mt-3">
                                    <h4>Update Status</h4>
                                    <form method="POST" class="status-form">
                                        <div class="form-group">
                                            <select name="status" class="form-control">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                <?php if ($order['delivery_type'] === 'delivery'): ?>
                                                    <option value="ready_for_delivery" <?php echo $order['status'] === 'ready_for_delivery' ? 'selected' : ''; ?>>Ready for Delivery</option>
                                                    <option value="out_for_delivery" <?php echo $order['status'] === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <?php else: ?>
                                                    <option value="ready_for_pickup" <?php echo $order['status'] === 'ready_for_pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                    <option value="picked_up" <?php echo $order['status'] === 'picked_up' ? 'selected' : ''; ?>>Picked Up</option>
                                                <?php endif; ?>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-items card">
                        <div class="card-header">
                            <h3>Order Items</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($orderItems)): ?>
                                <p class="text-center">No items found for this order.</p>
                            <?php else: ?>
                                <div class="order-items-list">
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="order-item">
                                            <div class="order-item-image">
                                                <?php if (!empty($item['image_url'])): ?>
                                                    <img src="../../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                <?php else: ?>
                                                    <div class="no-image">
                                                        <i class="fas fa-pizza-slice"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="order-item-details">
                                                <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                                <div class="item-meta">
                                                    <span>Quantity: <?php echo $item['quantity']; ?></span>
                                                    <span>Price: <?php echo formatPrice($item['unit_price']); ?></span>
                                                </div>
                                                
                                                <?php if (!empty($item['customizations'])): ?>
                                                    <div class="item-customizations">
                                                        <h5>Customizations</h5>
                                                        <ul>
                                                            <?php foreach ($item['customizations'] as $customization): ?>
                                                                <li>
                                                                    <?php echo htmlspecialchars($customization['ingredient_name']); ?> 
                                                                    (<?php echo formatPrice($customization['ingredient_price']); ?>)
                                                                    - <?php echo $customization['is_added'] ? 'Added' : 'Removed'; ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($item['special_instructions'])): ?>
                                                    <div class="item-instructions">
                                                        <h5>Special Instructions</h5>
                                                        <p><?php echo nl2br(htmlspecialchars($item['special_instructions'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="item-total">
                                                    Subtotal: <?php echo formatPrice($item['unit_price'] * $item['quantity']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-error">Order not found.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <style>
    .order-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .detail-label {
        font-weight: 600;
        width: 150px;
        flex-shrink: 0;
    }
    
    .detail-value {
        flex: 1;
    }
    
    .detail-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .status-form {
        display: flex;
        gap: 1rem;
        align-items: end;
    }
    
    .status-form .form-group {
        flex: 1;
    }
    
    .order-items-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .order-item {
        display: flex;
        gap: 1rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
    }
    
    .order-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .order-item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .order-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .no-image {
        width: 100%;
        height: 100%;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .no-image i {
        font-size: 2rem;
        color: #aaa;
    }
    
    .order-item-details {
        flex: 1;
    }
    
    .order-item-details h4 {
        margin: 0 0 0.5rem;
        font-size: 1.1rem;
    }
    
    .item-meta {
        display: flex;
        gap: 1rem;
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
    }
    
    .item-customizations h5,
    .item-instructions h5 {
        margin: 0.75rem 0 0.25rem;
        font-size: 0.9rem;
        color: #555;
    }
    
    .item-customizations ul {
        margin: 0;
        padding-left: 1.25rem;
        font-size: 0.9rem;
    }
    
    .item-instructions p {
        margin: 0;
        font-size: 0.9rem;
        color: #555;
    }
    
    .item-total {
        margin-top: 0.75rem;
        font-weight: 600;
        font-size: 1rem;
        color: var(--primary-color);
        text-align: right;
    }
    
    @media (max-width: 992px) {
        .order-detail-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html> 