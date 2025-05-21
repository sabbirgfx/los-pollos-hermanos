<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

try {
    // Fetch all products
    $stmt = $conn->query("SELECT p.*, c.name as category_name 
                        FROM products p 
                        JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Products in Database:</h1>";
    echo "<pre>";
    print_r($products);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 