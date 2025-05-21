<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';

// Function to establish database connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=localhost;dbname=" . DB_NAME;
        $conn = new PDO($dsn, "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Check if products already exist
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "Products already exist in database. Skipping seed.";
        $conn->rollBack();
        exit;
    }
    
    // Get pizza category ID
    $stmt = $conn->query("SELECT id FROM categories WHERE name = 'Pizzas'");
    $pizza_category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pizza_category) {
        throw new Exception("Pizza category not found!");
    }
    
    $pizza_category_id = $pizza_category['id'];
    
    // Insert pizzas
    $pizzas = [
        [
            'name' => 'Classic Margherita',
            'description' => 'Fresh tomatoes, mozzarella, basil, and our signature sauce on a perfectly crispy crust',
            'price' => 14.99,
            'image_url' => 'assets/images/pizzas/margherita.jpg'
        ],
        [
            'name' => 'Pepperoni Supreme',
            'description' => 'Loaded with premium pepperoni, extra cheese, and our special herb-infused tomato sauce',
            'price' => 16.99,
            'image_url' => 'assets/images/pizzas/pepperoni.jpg'
        ],
        [
            'name' => 'BBQ Chicken Delight',
            'description' => 'Grilled chicken, red onions, and bell peppers, drizzled with our house-made BBQ sauce',
            'price' => 17.99,
            'image_url' => 'assets/images/pizzas/bbq-chicken.jpg'
        ],
        [
            'name' => 'Vegetarian Paradise',
            'description' => 'Fresh mushrooms, bell peppers, olives, onions, and tomatoes with premium mozzarella',
            'price' => 15.99,
            'image_url' => 'assets/images/pizzas/veggie.jpg'
        ],
        [
            'name' => 'Meat Lovers Dream',
            'description' => 'Pepperoni, Italian sausage, bacon, ham, and ground beef for the ultimate meat experience',
            'price' => 18.99,
            'image_url' => 'assets/images/pizzas/meat-lovers.jpg'
        ],
        [
            'name' => 'Hawaiian Special',
            'description' => 'Sweet pineapple chunks, premium ham, and extra cheese for a tropical twist',
            'price' => 16.99,
            'image_url' => 'assets/images/pizzas/hawaiian.jpg'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, image_url, is_available) VALUES (?, ?, ?, ?, ?, 1)");
    
    foreach ($pizzas as $pizza) {
        $stmt->execute([
            $pizza_category_id,
            $pizza['name'],
            $pizza['description'],
            $pizza['price'],
            $pizza['image_url']
        ]);
        echo "Added pizza: " . $pizza['name'] . "<br>";
    }
    
    // Commit transaction
    $conn->commit();
    echo "Successfully added all pizzas to the database!";
    
} catch (Exception $e) {
    // Roll back transaction on error
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?> 