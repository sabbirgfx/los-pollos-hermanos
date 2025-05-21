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

// Delete ingredient
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $ingredientId = $_GET['delete'];
    
    try {
        // Check if ingredient exists
        $stmt = $conn->prepare("SELECT id FROM ingredients WHERE id = ?");
        $stmt->execute([$ingredientId]);
        
        if (!$stmt->fetch()) {
            $error = "Ingredient not found.";
        } else {
            // Check if this ingredient is used in any product
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_ingredients WHERE ingredient_id = ?");
            $stmt->execute([$ingredientId]);
            $inUse = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
            
            if ($inUse) {
                $error = "Cannot delete: this ingredient is used in one or more products.";
            } else {
                // Delete ingredient
                $stmt = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
                $stmt->execute([$ingredientId]);
                
                $message = "Ingredient deleted successfully.";
            }
        }
    } catch (PDOException $e) {
        $error = "Error deleting ingredient: " . $e->getMessage();
    }
}

// Toggle availability
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $ingredientId = $_GET['toggle'];
    
    try {
        // Get current status
        $stmt = $conn->prepare("SELECT is_available FROM ingredients WHERE id = ?");
        $stmt->execute([$ingredientId]);
        $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ingredient) {
            $error = "Ingredient not found.";
        } else {
            // Toggle status
            $newStatus = $ingredient['is_available'] ? 0 : 1;
            $stmt = $conn->prepare("UPDATE ingredients SET is_available = ? WHERE id = ?");
            $stmt->execute([$newStatus, $ingredientId]);
            
            $statusText = $newStatus ? "available" : "unavailable";
            $message = "Ingredient marked as $statusText.";
        }
    } catch (PDOException $e) {
        $error = "Error updating ingredient: " . $e->getMessage();
    }
}

// Handle add/edit ingredient
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ingredientId = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $error = "Ingredient name is required.";
    } elseif ($price < 0) {
        $error = "Price cannot be negative.";
    } else {
        try {
            if ($ingredientId) {
                // Update existing ingredient
                $stmt = $conn->prepare("UPDATE ingredients SET name = ?, description = ?, price = ?, is_available = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $is_available, $ingredientId]);
                $message = "Ingredient updated successfully.";
            } else {
                // Add new ingredient
                $stmt = $conn->prepare("INSERT INTO ingredients (name, description, price, is_available) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $is_available]);
                $message = "Ingredient added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get ingredient for editing
$editIngredient = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM ingredients WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editIngredient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$editIngredient) {
            $error = "Ingredient not found.";
        }
    } catch (PDOException $e) {
        $error = "Error fetching ingredient: " . $e->getMessage();
    }
}

// Get all ingredients
try {
    $stmt = $conn->query("SELECT * FROM ingredients ORDER BY name");
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching ingredients: " . $e->getMessage();
    $ingredients = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredient Management - Los Pollos Hermanos</title>
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
                <h1>Ingredient Management</h1>
                <p>Manage toppings and ingredients for pizza and other items</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h3><?php echo $editIngredient ? 'Edit Ingredient' : 'Add New Ingredient'; ?></h3>
                </div>
                <div class="card-body">
                    <form class="admin-form" method="POST">
                        <?php if ($editIngredient): ?>
                            <input type="hidden" name="id" value="<?php echo $editIngredient['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?php echo $editIngredient ? htmlspecialchars($editIngredient['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price" class="form-label">Price ($) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                    value="<?php echo $editIngredient ? htmlspecialchars($editIngredient['price']) : '0.00'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo $editIngredient ? htmlspecialchars($editIngredient['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="is_available" value="1" 
                                      <?php echo ($editIngredient && $editIngredient['is_available']) ? 'checked' : ''; ?>>
                                Ingredient is available
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editIngredient ? 'Update Ingredient' : 'Add Ingredient'; ?>
                            </button>
                            
                            <?php if ($editIngredient): ?>
                                <a href="ingredients.php" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Ingredients</h3>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($ingredients)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No ingredients found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($ingredients as $ingredient): ?>
                                    <tr>
                                        <td><?php echo $ingredient['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ingredient['name']); ?></td>
                                        <td><?php echo htmlspecialchars($ingredient['description']); ?></td>
                                        <td><?php echo formatPrice($ingredient['price']); ?></td>
                                        <td>
                                            <?php if($ingredient['is_available']): ?>
                                                <span class="status-badge status-ready_for_delivery">Available</span>
                                            <?php else: ?>
                                                <span class="status-badge status-cancelled">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <a href="ingredients.php?edit=<?php echo $ingredient['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="ingredients.php?toggle=<?php echo $ingredient['id']; ?>" class="btn btn-sm btn-outline">
                                                    <?php echo $ingredient['is_available'] ? 'Disable' : 'Enable'; ?>
                                                </a>
                                                <a href="ingredients.php?delete=<?php echo $ingredient['id']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this ingredient?');">Delete</a>
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