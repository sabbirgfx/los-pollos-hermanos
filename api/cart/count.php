<?php
session_start();
header('Content-Type: application/json');

// Initialize response
$response = ['success' => true, 'count' => 0];

// Get cart count
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $response['count'] = count($_SESSION['cart']);
}

echo json_encode($response);
?> 