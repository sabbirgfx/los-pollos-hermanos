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

// Delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = $_GET['delete'];
    
    // Don't allow deleting self (current admin)
    if ($userId == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        try {
            // Check if user exists and not an admin (prevent deleting other admins)
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                $error = "User not found.";
            } else if ($userData['role'] == 'admin' && $_SESSION['user_id'] != 1) {
                $error = "You don't have permission to delete an admin user.";
            } else {
                try {
                    // Start transaction
                    $conn->beginTransaction();
                    
                    // First, get all orders for this user
                    $stmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $orders = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Delete order items for all user's orders
                    if (!empty($orders)) {
                        $placeholders = str_repeat('?,', count($orders) - 1) . '?';
                        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)");
                        $stmt->execute($orders);
                    }
                    
                    // Delete user's orders
                    $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    
                    // Now delete the user
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    
                    // Commit transaction
                    $conn->commit();
                    $message = "User and their associated data deleted successfully.";
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $conn->rollBack();
                    $error = "Error deleting user: " . $e->getMessage();
                }
            }
        } catch (PDOException $e) {
            $error = "Error fetching user data: " . $e->getMessage();
        }
    }
}

// Get all users
try {
    $stmt = $conn->query("SELECT * FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Los Pollos Hermanos</title>
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
                <h1>User Management</h1>
                <p>Manage system users and employees</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Users</h3>
                    <a href="user_form.php" class="btn btn-primary">Add New User</a>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="action-btn-group">
                                                <a href="user_form.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
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
</body>
</html> 