<?php
require_once '../config/database.php';

// Initialize database connection
$conn = getDBConnection();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if request is POST and has JSON content
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Initialize response array
    $response = ['success' => false];
    
    // Validate cart
    if (empty($_SESSION['cart'])) {
        $response['message'] = 'Cart is empty';
        echo json_encode($response);
        exit;
    }
    
    // Calculate order total
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.08;
    $total = $subtotal + $tax;
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Insert order
        $order_query = "INSERT INTO orders (user_id, total_amount, status, delivery_type, delivery_address, 
                                          payment_method, payment_status, estimated_delivery_time) 
                       VALUES (?, ?, 'pending', ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 45 MINUTE))";
        
        $stmt = $conn->prepare($order_query);
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $stmt->execute([
            $user_id,
            $total,
            $data['delivery_type'],
            $data['address'],
            $data['payment_method']
        ]);
        $order_id = $conn->lastInsertId();
        
        // Insert order items
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, special_instructions) 
                      VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($item_query);
        
        foreach ($_SESSION['cart'] as $item) {
            // Prepare special instructions including customization data
            $instructions = $item['instructions'] ?? null;
            
            // If it's a customized pizza, add customization details to instructions
            if (isset($item['is_customized']) && $item['is_customized'] && isset($item['customization'])) {
                $customizationText = "Pizza Customizations: ";
                $customizationText .= "Crust: " . ucfirst($item['customization']['crust_size']) . ", ";
                $customizationText .= "Sauce: " . ucfirst($item['customization']['sauce']) . ", ";
                $customizationText .= "Cheese: " . ucfirst($item['customization']['cheese']);
                
                if (!empty($item['customization']['toppings'])) {
                    // Get topping names
                    $toppingNames = [];
                    $placeholders = str_repeat('?,', count($item['customization']['toppings']) - 1) . '?';
                    $toppingStmt = $conn->prepare("SELECT name FROM ingredients WHERE id IN ($placeholders)");
                    $toppingStmt->execute($item['customization']['toppings']);
                    while ($topping = $toppingStmt->fetch(PDO::FETCH_ASSOC)) {
                        $toppingNames[] = $topping['name'];
                    }
                    $customizationText .= ", Toppings: " . implode(', ', $toppingNames);
                }
                
                // Combine with special instructions
                if (!empty($instructions)) {
                    $instructions .= " | " . $customizationText;
                } else {
                    $instructions = $customizationText;
                }
            }
            
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $instructions
            ]);
            $order_item_id = $conn->lastInsertId();
            
            // Insert order item ingredients if it's a customized pizza
            if (isset($item['is_customized']) && $item['is_customized'] && isset($item['customization']['toppings'])) {
                $ingredient_query = "INSERT INTO order_item_ingredients (order_item_id, ingredient_id, is_added) 
                                   VALUES (?, ?, 1)";
                $stmt2 = $conn->prepare($ingredient_query);
                
                foreach ($item['customization']['toppings'] as $ingredient_id) {
                    $stmt2->execute([$order_item_id, $ingredient_id]);
                }
            }
        }
        
        // Process payment if online payment
        if ($data['payment_method'] === 'online') {
            // Here you would integrate with a payment gateway
            // For now, we'll just simulate a successful payment
            $payment_success = true;
            
            if ($payment_success) {
                $update_query = "UPDATE orders SET payment_status = 'completed' WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->execute([$order_id]);
            } else {
                throw new Exception('Payment processing failed');
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Send success response
        $response['success'] = true;
        $response['order_id'] = $order_id;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $response['message'] = 'Error processing order: ' . $e->getMessage();
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not a valid request, return error
header('HTTP/1.1 400 Bad Request');
echo json_encode(['success' => false, 'error' => 'Invalid request']); 