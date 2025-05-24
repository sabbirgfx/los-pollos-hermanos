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

// Handle actions
$message = '';
$error = '';

// Handle status update
if (isset($_GET['update_status']) && is_numeric($_GET['update_status']) && isset($_GET['status'])) {
    $orderId = $_GET['update_status'];
    $status = $_GET['status'];
    
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

// Get filters
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$query = "SELECT o.*, u.username, u.first_name, u.last_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE 1=1 ";
$params = [];

if (!empty($status_filter)) {
    $query .= "AND o.status = ? ";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    $query .= "AND DATE(o.created_at) = ? ";
    $params[] = $date_filter;
}

if (!empty($search)) {
    $query .= "AND (o.id LIKE ? OR u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?) ";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= "ORDER BY o.id DESC";

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
    <title>Order Management - Los Pollos Hermanos</title>
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
                <h1>Order Management</h1>
                <p>View and manage all customer orders</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Filters</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status_filter" class="form-label">Status</label>
                                <select name="status_filter" id="status_filter" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="ready_for_delivery" <?php echo $status_filter === 'ready_for_delivery' ? 'selected' : ''; ?>>Ready for Delivery</option>
                                    <option value="out_for_delivery" <?php echo $status_filter === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="ready_for_pickup" <?php echo $status_filter === 'ready_for_pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                    <option value="picked_up" <?php echo $status_filter === 'picked_up' ? 'selected' : ''; ?>>Picked Up</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="date_filter" class="form-label">Date</label>
                                <input type="date" name="date_filter" id="date_filter" class="form-control" value="<?php echo $date_filter; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Order ID, username, name..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="orders.php" class="btn btn-outline">Clear Filters</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Orders</h3>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?> (<?php echo htmlspecialchars($order['username']); ?>)</td>
                                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                                        <td><?php echo ucfirst($order['delivery_type']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo getOrderStatusText($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="action-btn-group">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                                                
                                                <?php if($order['status'] !== 'delivered' && $order['status'] !== 'picked_up' && $order['status'] !== 'cancelled'): ?>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline dropdown-toggle">Update Status</button>
                                                        <div class="dropdown-content">
                                                            <?php if($order['status'] === 'pending'): ?>
                                                                <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=preparing">Mark as Preparing</a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($order['status'] === 'preparing' && $order['delivery_type'] === 'delivery'): ?>
                                                                <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=ready_for_delivery">Mark as Ready for Delivery</a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($order['status'] === 'ready_for_delivery'): ?>
                                                                <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=out_for_delivery">Mark as Out for Delivery</a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($order['status'] === 'out_for_delivery'): ?>
                                                                <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=delivered">Mark as Delivered</a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($order['status'] === 'preparing' && $order['delivery_type'] === 'pickup'): ?>
                                                                <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=ready_for_pickup">Mark as Ready for Pickup</a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($order['status'] === 'ready_for_pickup'): ?>
                                                                <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=picked_up">Mark as Picked Up</a>
                                                            <?php endif; ?>
                                                            
                                                            <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=cancelled" 
                                                               onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <style>
    /* Dropdown styles */
    .dropdown {
        position: relative;
        display: inline-block;
    }
    
    .dropdown-toggle {
        cursor: pointer;
    }
    
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: #fff;
        min-width: 200px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        z-index: 1;
        border-radius: 6px;
        overflow: hidden;
    }
    
    .dropdown-content a {
        color: #333;
        padding: 10px 15px;
        text-decoration: none;
        display: block;
        text-align: left;
        font-size: 0.9rem;
    }
    
    .dropdown-content a:hover {
        background-color: #f5f5f5;
    }
    
    .dropdown:hover .dropdown-content {
        display: block;
    }
    
    .filter-form .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .filter-form .form-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-form .form-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }
    </style>
</body>
</html> 