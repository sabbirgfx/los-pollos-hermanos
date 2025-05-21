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

// Handle actions
$message = '';
$error = '';

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $productId = $_GET['delete'];
    
    try {
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if (!$stmt->fetch()) {
            $error = "Product not found.";
        } else {
            // Delete product ingredients associations first
            $stmt = $conn->prepare("DELETE FROM product_ingredients WHERE product_id = ?");
            $stmt->execute([$productId]);
            
            // Delete product
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            
            $message = "Product deleted successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error deleting product: " . $e->getMessage();
    }
}

// Handle availability toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $productId = $_GET['toggle'];
    
    try {
        // Get current availability
        $stmt = $conn->prepare("SELECT is_available FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $error = "Product not found.";
        } else {
            // Toggle availability
            $newStatus = $product['is_available'] ? 0 : 1;
            $stmt = $conn->prepare("UPDATE products SET is_available = ? WHERE id = ?");
            $stmt->execute([$newStatus, $productId]);
            
            $statusText = $newStatus ? "available" : "unavailable";
            $message = "Product marked as $statusText.";
        }
    } catch (PDOException $e) {
        $error = "Error updating product: " . $e->getMessage();
    }
}

// Get all products with their categories
try {
    $stmt = $conn->query("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id
                          ORDER BY p.category_id, p.name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching products: " . $e->getMessage();
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Los Pollos Hermanos</title>
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
                <h1>Product Management</h1>
                <p>Manage pizza and non-pizza items</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Products</h3>
                    <a href="product_form.php" class="btn btn-primary">Add New Product</a>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No products found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; border-radius: 5px; background-color: #eee; display: flex; justify-content: center; align-items: center;">
                                                    <i class="fas fa-image" style="color: #aaa;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td><?php echo formatPrice($product['price']); ?></td>
                                        <td>
                                            <?php if($product['is_available']): ?>
                                                <span class="status-badge status-ready_for_delivery">Available</span>
                                            <?php else: ?>
                                                <span class="status-badge status-cancelled">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <a href="product_form.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="products.php?toggle=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline">
                                                    <?php echo $product['is_available'] ? 'Disable' : 'Enable'; ?>
                                                </a>
                                                <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
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