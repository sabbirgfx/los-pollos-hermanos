<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Check if user is logged in and has delivery staff role
if (!isLoggedIn() || !hasRole('delivery_staff')) {
    redirect('../../index.php');
    exit();
}

// Initialize database connection
$conn = getDBConnection();

// Get order ID
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    redirect('modules/delivery/orders.php');
    exit();
}

// Handle actions
$message = '';
$error = '';

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['status'])) {
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

// Get order details
try {
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.first_name, u.last_name, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.delivery_type = 'delivery' 
        AND o.status IN ('ready_for_delivery', 'out_for_delivery', 'delivered')
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        redirect('modules/delivery/orders.php');
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

    // Get order item customizations
    foreach ($orderItems as $key => $item) {
        $stmt = $conn->prepare("
            SELECT oii.*, i.name as ingredient_name
            FROM order_item_ingredients oii
            JOIN ingredients i ON oii.ingredient_id = i.id
            WHERE oii.order_item_id = ?
        ");
        $stmt->execute([$item['id']]);
        $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $orderItems[$key]['customizations'] = $customizations;
    }

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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/staff.css">
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
                        <span class="info-label">Customer:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Delivery Address:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Time:</span>
                        <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo getPaymentMethodText($order['payment_method']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Status:</span>
                        <span class="info-value"><?php echo ucfirst($order['payment_status']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value info-total"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                </div>

                <?php if ($order['status'] === 'ready_for_delivery'): ?>
                    <div class="order-actions">
                        <form method="POST" class="status-form">
                            <input type="hidden" name="status" value="out_for_delivery">
                            <button type="submit" name="update_status" class="btn btn-primary">Start Delivery</button>
                        </form>
                    </div>
                <?php elseif ($order['status'] === 'out_for_delivery'): ?>
                    <div class="order-actions">
                        <form method="POST" class="status-form">
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" name="update_status" class="btn btn-primary">Mark as Delivered</button>
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
                            
                            <?php if (!empty($item['customizations'])): ?>
                                <div class="item-customizations">
                                    <strong>Additional Toppings:</strong>
                                    <ul>
                                        <?php foreach ($item['customizations'] as $customization): ?>
                                            <li>
                                                <?php echo htmlspecialchars($customization['ingredient_name']); ?> 
                                                - <?php echo $customization['is_added'] ? 'Added' : 'Removed'; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($customizationInfo && !empty($customizationInfo['regular_instructions'])): ?>
                                <div class="special-instructions">
                                    <strong>Special Instructions:</strong>
                                    <p><?php echo htmlspecialchars($customizationInfo['regular_instructions']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <style>
        .info-total {
            font-weight: bold;
            font-size: 1.2em;
            color: var(--primary-color);
        }
        
        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .order-info-card, .order-items-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        
        .order-info-card h3, .order-items-card h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .info-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .info-row {
            display: flex;
        }
        
        .info-label {
            font-weight: 600;
            width: 140px;
            flex-shrink: 0;
        }
        
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .item-card {
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 1rem;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .item-header h4 {
            margin: 0;
        }
        
        .item-quantity {
            background: #f5f5f5;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .item-description {
            margin: 0.5rem 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .special-instructions, .item-customizations {
            margin-top: 0.75rem;
            font-size: 0.9rem;
        }
        
        .special-instructions p, .item-customizations ul {
            margin: 0.25rem 0 0;
        }
        
        .item-customizations ul {
            padding-left: 1.25rem;
        }
    </style>
</body>
</html> 