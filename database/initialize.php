<?php
require_once '../config/database.php';

try {
    // Connect to MySQL without database
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Connected to MySQL successfully.</p>";
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<p>Database '" . DB_NAME . "' created or already exists.</p>";
    
    // Use the database
    $conn->exec("USE " . DB_NAME);
    
    // Create tables
    $schemaFile = file_get_contents('schema.sql');
    $queries = explode(';', $schemaFile);
    
    foreach ($queries as $query) {
        if (trim($query)) {
            try {
                $conn->exec($query);
            } catch (PDOException $e) {
                echo "<p>Error executing query: " . $e->getMessage() . "</p>";
                echo "<p>Query: " . $query . "</p>";
            }
        }
    }
    
    echo "<p>Schema created successfully.</p>";
    
    // Insert default pizzas
    $conn = getDBConnection();
    
    // Check if products already exist
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "<p>Products already exist in database. Skipping product seed.</p>";
    } else {
        // Get pizza category ID 
        $stmt = $conn->query("SELECT id FROM categories WHERE name = 'Pizzas'");
        $pizza_category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pizza_category) {
            echo "<p>Pizza category not found! Creating categories...</p>";
            
            // Insert default categories
            $conn->exec("INSERT INTO categories (name, description) VALUES
                ('Pizzas', 'Our signature pizzas'),
                ('Sides', 'Delicious sides and appetizers'),
                ('Drinks', 'Refreshing beverages'),
                ('Desserts', 'Sweet treats to complete your meal')");
            
            $stmt = $conn->query("SELECT id FROM categories WHERE name = 'Pizzas'");
            $pizza_category = $stmt->fetch(PDO::FETCH_ASSOC);
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
            echo "<p>Added pizza: " . $pizza['name'] . "</p>";
        }
        
        echo "<p>Successfully added all pizzas to the database!</p>";
    }
    
    echo "<p>Database initialization complete!</p>";
    echo "<p><a href='../index.php'>Go to Homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 