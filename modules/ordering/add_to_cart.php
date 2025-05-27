<?php
// Start output buffering to catch any unwanted output
ob_start();

// Set error handling to suppress warnings/notices from being output
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Clear any existing output
ob_clean();

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$is_customized = isset($_POST['is_customized']) ? (bool)$_POST['is_customized'] : false;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_available = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
        exit;
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $cart_item = [
        'id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'image_url' => $product['image_url']
    ];

    // Handle customized pizza
    if ($is_customized && isset($_POST['customization'])) {
        $customization = json_decode($_POST['customization'], true);
        
        if (!$customization) {
            echo json_encode(['success' => false, 'message' => 'Invalid customization data']);
            exit;
        }

        // Add customization details to cart item
        $cart_item['is_customized'] = true;
        $cart_item['customization'] = $customization;
        
        // Update price based on customization
        $cart_item['price'] = $customization['total_price'];
        
        // Add customization details to name
        $cart_item['name'] .= sprintf(
            ' (%s, %s sauce, %s cheese)',
            ucfirst($customization['crust_size']),
            ucfirst($customization['sauce']),
            ucfirst($customization['cheese'])
        );

        // Add toppings to name if any
        if (!empty($customization['toppings'])) {
            $toppings = [];
            $stmt = $conn->prepare("SELECT name FROM ingredients WHERE id IN (" . str_repeat('?,', count($customization['toppings']) - 1) . "?)");
            $stmt->execute($customization['toppings']);
            while ($topping = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $toppings[] = $topping['name'];
            }
            if (!empty($toppings)) {
                $cart_item['name'] .= ' with ' . implode(', ', $toppings);
            }
        }
    }

    // Check if item already exists in cart
    $item_exists = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] === $product_id) {
            if (!$is_customized || 
                ($is_customized && 
                 isset($item['customization']) && 
                 $item['customization'] === $cart_item['customization'])) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $item_exists = true;
                break;
            }
        }
    }

    // Add new item if it doesn't exist
    if (!$item_exists) {
        $_SESSION['cart'][] = $cart_item;
    }

    // Calculate total items in cart
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cartCount' => $cart_count
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 