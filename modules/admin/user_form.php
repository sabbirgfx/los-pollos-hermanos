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

// Check if editing existing user or creating new one
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = ($userId !== null);

$user = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => '',
    'address' => '',
    'role' => 'customer'
];

// Get existing user data if editing
if ($isEdit) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            redirect('modules/admin/users.php');
            exit();
        }
        
        $user = $userData;
    } catch (PDOException $e) {
        $error = "Error fetching user data: " . $e->getMessage();
    }
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $role = sanitizeInput($_POST['role']);
    $password = $_POST['password'] ?? null;
    
    // Validation
    if (empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
        $error = "Please fill in all required fields.";
    } elseif (!isValidEmail($email)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Check if username exists (for new user or if username changed)
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId ?? 0]);
            if ($stmt->fetch()) {
                $error = "Username already exists.";
            } else {
                // Check if email exists (for new user or if email changed)
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId ?? 0]);
                if ($stmt->fetch()) {
                    $error = "Email already exists.";
                } else {
                    if ($isEdit) {
                        // Update existing user
                        if (!empty($password)) {
                            // Update with password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, 
                                                 last_name = ?, phone = ?, address = ?, password = ?, role = ? 
                                                 WHERE id = ?");
                            $stmt->execute([$username, $email, $first_name, $last_name, $phone, $address, 
                                          $hashed_password, $role, $userId]);
                        } else {
                            // Update without changing password
                            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, 
                                                 last_name = ?, phone = ?, address = ?, role = ? 
                                                 WHERE id = ?");
                            $stmt->execute([$username, $email, $first_name, $last_name, $phone, $address, 
                                          $role, $userId]);
                        }
                        $message = "User updated successfully.";
                    } else {
                        // Create new user
                        if (empty($password)) {
                            $error = "Password is required for new users.";
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("INSERT INTO users (username, email, first_name, last_name, 
                                                 phone, address, password, role) 
                                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$username, $email, $first_name, $last_name, $phone, $address, 
                                          $hashed_password, $role]);
                            $message = "User created successfully.";
                            
                            // Reset form for new entry
                            $isEdit = false;
                            $userId = null;
                            $user = [
                                'username' => '',
                                'email' => '',
                                'first_name' => '',
                                'last_name' => '',
                                'phone' => '',
                                'address' => '',
                                'role' => 'customer'
                            ];
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit User' : 'Add User'; ?> - Los Pollos Hermanos</title>
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
                <h1><?php echo $isEdit ? 'Edit User' : 'Add New User'; ?></h1>
                <p>
                    <a href="users.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form class="admin-form" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                    value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                    value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="kitchen_staff" <?php echo $user['role'] === 'kitchen_staff' ? 'selected' : ''; ?>>Kitchen Staff</option>
                                    <option value="delivery_staff" <?php echo $user['role'] === 'delivery_staff' ? 'selected' : ''; ?>>Delivery Staff</option>
                                    <option value="counter_staff" <?php echo $user['role'] === 'counter_staff' ? 'selected' : ''; ?>>Counter Staff</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <?php echo $isEdit ? 'Password (leave blank to keep current)' : 'Password *'; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                <?php echo !$isEdit ? 'required' : ''; ?>>
                            <?php if ($isEdit): ?>
                                <div class="form-text">Leave blank to keep current password.</div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo $isEdit ? 'Update User' : 'Add User'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 