<?php
session_start();
header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if data is valid
if (!$data || !isset($data['product_id']) || !isset($data['quantity'])) {
    $response['message'] = 'Invalid request data';
    echo json_encode($response);
    exit;
}

// Sanitize input
$product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

// Validate input
if (!$product_id || $product_id <= 0) {
    $response['message'] = 'Invalid product ID';
    echo json_encode($response);
    exit;
}

if (!$quantity || $quantity <= 0) {
    $response['message'] = 'Invalid quantity';
    echo json_encode($response);
    exit;
}

// Get database connection
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$conn = getDBConnection();

try {
    // Fetch product information
    $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = ? AND is_available = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $response['message'] = 'Product not found or unavailable';
        echo json_encode($response);
        exit;
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    // If not found, add to cart
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image_url' => $product['image_url'] ?? 'assets/images/default-product.jpg'
        ];
    }

    $response['success'] = true;
    $response['message'] = 'Product added to cart';
    $response['cart_count'] = count($_SESSION['cart']);
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response); 