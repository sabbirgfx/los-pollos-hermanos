<?php
require_once __DIR__ . '/../config/database.php';

// Initialize database connection if not already initialized
if (!isset($conn)) {
    $conn = getDBConnection();
}

/**
 * Utility functions for the Los Pollos Hermanos pizza ordering system
 */

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate a random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Redirect to a specific page
 */
function redirect($page) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . $host . '/Los%20Pollos%20Hermanos';
    
    if (strpos($page, 'http') === 0) {
        header("Location: $page");
    } else {
        header("Location: $base_url/$page");
    }
    exit();
}

/**
 * Display error message
 */
function displayError($message) {
    return "<div class='alert alert-error'>$message</div>";
}

/**
 * Display success message
 */
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

/**
 * Format price
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Calculate order total
 */
function calculateOrderTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/**
 * Get order status display text
 */
function getOrderStatusText($status) {
    $statusMap = [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'ready_for_delivery' => 'Ready for Delivery',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'ready_for_pickup' => 'Ready for Pickup',
        'picked_up' => 'Picked Up',
        'cancelled' => 'Cancelled'
    ];
    return $statusMap[$status] ?? $status;
}

/**
 * Get delivery type display text
 */
function getDeliveryTypeText($type) {
    return $type === 'delivery' ? 'Delivery' : 'Pickup';
}

/**
 * Get payment method display text
 */
function getPaymentMethodText($method) {
    return $method === 'online' ? 'Online Payment' : 'Cash on Delivery';
}

/**
 * Check if product is available
 */
function isProductAvailable($productId) {
    global $conn;
    $stmt = $conn->prepare("SELECT is_available FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result && $result['is_available'];
}

/**
 * Get product details
 */
function getProductDetails($productId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get category details
 */
function getCategoryDetails($categoryId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user details
 */
function getUserDetails($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get order details
 */
function getOrderDetails($orderId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT o.*, u.first_name, u.last_name, u.phone, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get order items
 */
function getOrderItems($orderId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customizations for each item
    foreach ($orderItems as $key => $item) {
        $stmt = $conn->prepare("
            SELECT oii.*, i.name as ingredient_name
            FROM order_item_ingredients oii
            JOIN ingredients i ON oii.ingredient_id = i.id
            WHERE oii.order_item_id = ?
        ");
        $stmt->execute([$item['id']]);
        $customizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $orderItems[$key]['customizations'] = $customizations;
    }
    
    return $orderItems;
}

/**
 * Update order status
 */
function updateOrderStatus($orderId, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $orderId]);
}

/**
 * Parse and format pizza customization details
 */
function formatPizzaCustomizations($specialInstructions) {
    if (empty($specialInstructions)) {
        return null;
    }
    
    // Check if this contains pizza customizations
    if (strpos($specialInstructions, 'Pizza Customizations:') !== false) {
        // Split by | to separate regular instructions from customizations
        $parts = explode(' | ', $specialInstructions);
        $customizationPart = '';
        $regularInstructions = '';
        
        foreach ($parts as $part) {
            if (strpos($part, 'Pizza Customizations:') !== false) {
                $customizationPart = $part;
            } else {
                $regularInstructions .= $part . ' ';
            }
        }
        
        if (!empty($customizationPart)) {
            // Remove "Pizza Customizations: " prefix
            $customizationDetails = str_replace('Pizza Customizations: ', '', $customizationPart);
            
            return [
                'has_customizations' => true,
                'customizations' => $customizationDetails,
                'regular_instructions' => trim($regularInstructions)
            ];
        }
    }
    
    return [
        'has_customizations' => false,
        'customizations' => '',
        'regular_instructions' => $specialInstructions
    ];
}

/**
 * Check if user can access specific module
 */
function canAccessModule($module) {
    if (!isLoggedIn()) {
        return false;
    }

    $role = $_SESSION['user_role'];
    
    switch ($module) {
        case 'admin':
            return $role === 'admin';
        case 'kitchen':
            return $role === 'kitchen_staff';
        case 'delivery':
            return $role === 'delivery_staff';
        case 'counter':
            return $role === 'counter_staff';
        case 'ordering':
            return $role === 'customer';
        default:
            return false;
    }
} 