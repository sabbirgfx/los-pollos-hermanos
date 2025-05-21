<?php
// Menu component for Los Pollos Hermanos Pizza Selection

// Define base path based on the file that includes this template
$isRoot = !isset($isModuleContext);
$basePath = $isRoot ? '' : '../../';

// Fetch pizzas from the database
if ($isRoot) {
    require_once 'config/database.php';
    require_once 'includes/functions.php';
} else {
    require_once '../../config/database.php';
    require_once '../../includes/functions.php';
}

$conn = getDBConnection();
$pizzaQuery = "SELECT p.* FROM products p 
               JOIN categories c ON p.category_id = c.id 
               WHERE c.name = 'Pizzas' ORDER BY p.name";
try {
    $stmt = $conn->query($pizzaQuery);
    $pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error loading pizzas: " . $e->getMessage() . "</p>";
    $pizzas = [];
}

// Mapping for pizza type to slug
$pizzaSlugs = [
    'Classic Margherita' => 'margherita',
    'Pepperoni Supreme' => 'pepperoni',
    'BBQ Chicken Delight' => 'bbq-chicken',
    'Vegetarian Paradise' => 'veggie',
    'Meat Lovers Dream' => 'meat-lovers',
    'Hawaiian Special' => 'hawaiian'
];
?>

<div class="menu-section">
    <h2 class="menu-title">Our Signature Pizzas</h2>
    <div class="pizza-grid">
        <?php foreach($pizzas as $pizza): 
            $pizzaSlug = $pizzaSlugs[$pizza['name']] ?? strtolower(str_replace(' ', '-', $pizza['name']));
            // Fix image path based on context
            $imagePath = $pizza['image_url'];
            if (strpos($imagePath, 'assets/') === 0 && !$isRoot) {
                $imagePath = '../../' . $imagePath;
            }
        ?>
        <div class="pizza-card">
            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($pizza['name']); ?>" class="pizza-image">
            <h3><?php echo htmlspecialchars($pizza['name']); ?></h3>
            <p class="description"><?php echo htmlspecialchars($pizza['description']); ?></p>
            <p class="price">$<?php echo number_format($pizza['price'], 2); ?></p>
            <button class="order-btn" data-pizza="<?php echo $pizzaSlug; ?>" data-id="<?php echo $pizza['id']; ?>">Order Now</button>
        </div>
        <?php endforeach; ?>

        <?php if (empty($pizzas)): ?>
        <div class="no-products">
            <p>No pizzas available at the moment. Please check back later.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.menu-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.menu-title {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 2rem;
    font-size: 2.5rem;
}

.pizza-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 1rem;
}

.pizza-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
    padding-bottom: 1rem;
}

.pizza-card:hover {
    transform: translateY(-5px);
}

.pizza-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.pizza-card h3 {
    color: #2c3e50;
    margin: 1rem;
    font-size: 1.5rem;
}

.description {
    color: #666;
    margin: 0.5rem 1rem;
    font-size: 0.9rem;
    min-height: 60px;
}

.price {
    color: #e74c3c;
    font-size: 1.4rem;
    font-weight: bold;
    margin: 1rem;
}

.order-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    margin: 0 1rem;
    transition: background 0.3s ease;
}

.order-btn:hover {
    background: #c0392b;
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 2rem;
    background: #f8f8f8;
    border-radius: 10px;
}

@media (max-width: 768px) {
    .pizza-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .menu-title {
        font-size: 2rem;
    }
}
</style> 