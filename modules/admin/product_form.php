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

// Check if editing existing product or creating new one
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = ($productId !== null);

$product = [
    'name' => '',
    'description' => '',
    'price' => '',
    'category_id' => '',
    'image_url' => '',
    'is_available' => 1,
];

// Get existing product data if editing
if ($isEdit) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $productData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$productData) {
            redirect('modules/admin/products.php');
            exit();
        }
        
        $product = $productData;
    } catch (PDOException $e) {
        $error = "Error fetching product data: " . $e->getMessage();
    }
}

// Get all categories
try {
    $stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching categories: " . $e->getMessage();
    $categories = [];
}

// Get all ingredients
try {
    $stmt = $conn->query("SELECT id, name, price FROM ingredients ORDER BY name");
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If editing, get product ingredients
    if ($isEdit && !empty($ingredients)) {
        $stmt = $conn->prepare("SELECT ingredient_id, is_default FROM product_ingredients WHERE product_id = ?");
        $stmt->execute([$productId]);
        $productIngredients = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
} catch (PDOException $e) {
    $error = "Error fetching ingredients: " . $e->getMessage();
    $ingredients = [];
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float) $_POST['price'];
    $category_id = (int) $_POST['category_id'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Handle image upload
    $image_url = $product['image_url']; // Default to current image
    
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../../uploads/products/';
        
        // Create the directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('product_') . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = 'uploads/products/' . $file_name;
        } else {
            $error = "Failed to upload image.";
        }
    }
    
    // Validation
    if (empty($name)) {
        $error = "Product name is required.";
    } elseif ($price <= 0) {
        $error = "Price must be greater than 0.";
    } elseif ($category_id <= 0) {
        $error = "Please select a category.";
    } else {
        try {
            $conn->beginTransaction();
            
            if ($isEdit) {
                // Update existing product
                $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, 
                                      category_id = ?, image_url = ?, is_available = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $category_id, $image_url, $is_available, $productId]);
                
                // Clear existing product ingredients
                $stmt = $conn->prepare("DELETE FROM product_ingredients WHERE product_id = ?");
                $stmt->execute([$productId]);
                
            } else {
                // Create new product
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, 
                                      image_url, is_available) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $category_id, $image_url, $is_available]);
                
                $productId = $conn->lastInsertId();
            }
            
            // Add product ingredients
            if ($productId && !empty($_POST['ingredients'])) {
                $stmt = $conn->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, is_default) 
                                      VALUES (?, ?, ?)");
                
                foreach ($_POST['ingredients'] as $ingredientId) {
                    $isDefault = isset($_POST['default_ingredients']) && in_array($ingredientId, $_POST['default_ingredients']);
                    $stmt->execute([$productId, $ingredientId, $isDefault ? 1 : 0]);
                }
            }
            
            $conn->commit();
            
            $message = ($isEdit ? "Product updated" : "Product created") . " successfully.";
            
            // Reset form for new entry if creating
            if (!$isEdit) {
                $product = [
                    'name' => '',
                    'description' => '',
                    'price' => '',
                    'category_id' => '',
                    'image_url' => '',
                    'is_available' => 1,
                ];
            }
            
        } catch (PDOException $e) {
            $conn->rollBack();
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
    <title><?php echo $isEdit ? 'Edit Product' : 'Add Product'; ?> - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    
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
                <h1><?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?></h1>
                <p>
                    <a href="products.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Products
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
                    <form class="admin-form" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price" class="form-label">Price ($) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01"
                                    value="<?php echo htmlspecialchars($product['price']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <?php if (!empty($product['image_url'])): ?>
                                    <div class="mt-2">
                                        <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="Current image" style="max-width: 100px; max-height: 100px;">
                                        <div class="form-text">Current image</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="is_available" value="1" 
                                      <?php echo $product['is_available'] ? 'checked' : ''; ?>>
                                Product is available
                            </label>
                        </div>
                        
                        <?php if (!empty($ingredients)): ?>
                            <div class="form-group">
                                <label class="form-label">Ingredients</label>
                                <div class="ingredient-list">
                                    <?php foreach($ingredients as $ingredient): ?>
                                        <div class="ingredient-item">
                                            <label class="form-checkbox">
                                                <input type="checkbox" name="ingredients[]" value="<?php echo $ingredient['id']; ?>"
                                                       <?php echo (isset($productIngredients) && array_key_exists($ingredient['id'], $productIngredients)) ? 'checked' : ''; ?>>
                                                <?php echo htmlspecialchars($ingredient['name']); ?> 
                                                (<?php echo formatPrice($ingredient['price']); ?>)
                                            </label>
                                            
                                            <label class="form-checkbox ml-3">
                                                <input type="checkbox" name="default_ingredients[]" value="<?php echo $ingredient['id']; ?>"
                                                       <?php echo (isset($productIngredients) && array_key_exists($ingredient['id'], $productIngredients) && $productIngredients[$ingredient['id']] == 1) ? 'checked' : ''; ?>>
                                                Default
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text">Check ingredients that can be added to this product. Mark "Default" for ingredients included by default.</div>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo $isEdit ? 'Update Product' : 'Add Product'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html> 