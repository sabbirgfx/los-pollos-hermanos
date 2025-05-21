<?php
session_start();

// Check if request is POST and has JSON content
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Initialize response array
    $response = ['success' => false];
    
    // Check if cart exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Handle remove item
    if (isset($data['remove']) && $data['remove'] === true) {
        if (isset($data['index']) && isset($_SESSION['cart'][$data['index']])) {
            unset($_SESSION['cart'][$data['index']]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
            $response['success'] = true;
        }
    }
    // Handle update quantity
    else if (isset($data['quantity']) && isset($data['index'])) {
        if (isset($_SESSION['cart'][$data['index']])) {
            $quantity = (int)$data['quantity'];
            if ($quantity >= 1 && $quantity <= 10) {
                $_SESSION['cart'][$data['index']]['quantity'] = $quantity;
                $response['success'] = true;
            }
        }
    }
    // Handle add item
    else if (isset($data['item'])) {
        $_SESSION['cart'][] = $data['item'];
        $response['success'] = true;
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not a valid request, return error
header('HTTP/1.1 400 Bad Request');
echo json_encode(['success' => false, 'error' => 'Invalid request']); 