<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Get database connection
$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    
    try {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $email, $phone, $user_id]);
        $message = "Profile updated successfully!";
    } catch (PDOException $e) {
        $message = "Error updating profile. Please try again.";
    }
}

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get order statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    $stmt = $conn->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    $pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

    $stmt = $conn->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status = 'delivered'");
    $stmt->execute([$user_id]);
    $total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;
} catch (PDOException $e) {
    $message = "Error fetching user data.";
    $user = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <h1>Welcome, <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>!</h1>
            <p>Manage your profile and view your order history</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-stats">
            <div class="stat-card">
                <i class="fas fa-shopping-bag"></i>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="stat-number"><?php echo $pending_orders; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i>
                <div class="stat-number">$<?php echo number_format($total_spent, 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-section">
                <h2>Personal Information</h2>
                <form method="POST" action="" class="profile-form">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <div class="profile-section">
                <h2>Recent Orders</h2>
                <?php
                try {
                    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute([$user_id]);
                    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($orders) > 0) {
                        echo '<div class="orders-list">';
                        foreach ($orders as $order) {
                            $status_class = 'status-' . strtolower($order['status']);
                            echo '<div class="order-item">';
                            echo '<p class="order-number">Order #' . htmlspecialchars($order['id']) . '</p>';
                            echo '<p class="order-date">Date: ' . date('M d, Y', strtotime($order['created_at'])) . '</p>';
                            echo '<p class="order-status ' . $status_class . '">' . htmlspecialchars($order['status']) . '</p>';
                            echo '<a href="../ordering/order_details.php?id=' . $order['id'] . '" class="btn btn-secondary">View Details</a>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No orders found.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error fetching orders.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 