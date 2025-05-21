<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('modules/auth/login.php');
    exit();
}

// Initialize database connection
$conn = getDBConnection();

// Get some statistics for the dashboard
try {
    // Total orders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Today's orders
    $stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $todayOrders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total products
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Recent orders
    $stmt = $conn->query("SELECT o.*, u.username FROM orders o 
                         JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle errors
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <?php include 'includes/admin_sidebar.php'; ?>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Total Orders</h3>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-value"><?php echo $totalOrders; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <i class="fas fa-calendar-day"></i>
                        <h3>Today's Orders</h3>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-value"><?php echo $todayOrders; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <i class="fas fa-users"></i>
                        <h3>Total Users</h3>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-value"><?php echo $totalUsers; ?></span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <i class="fas fa-pizza-slice"></i>
                        <h3>Total Products</h3>
                    </div>
                    <div class="stat-card-body">
                        <span class="stat-value"><?php echo $totalProducts; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-recent">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="orders.php" class="btn btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo getOrderStatusText($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View</a>
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
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 