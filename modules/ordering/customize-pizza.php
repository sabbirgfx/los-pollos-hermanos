<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Initialize database connection
$conn = getDBConnection();

// Get pizza ID from URL
$pizza_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get pizza details
$pizza = getProductDetails($pizza_id);

if (!$pizza || $pizza['category_id'] != 9) { // Category ID 9 is for pizzas
    redirect('menu.php');
}

// Get available toppings
try {
    $stmt = $conn->query("SELECT * FROM toppings WHERE is_available = 1 ORDER BY name");
    $toppings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching toppings: " . $e->getMessage();
}

// Define available options
$crust_sizes = [
    'small' => ['name' => 'Small (10")', 'price' => 0],
    'medium' => ['name' => 'Medium (12")', 'price' => 2],
    'large' => ['name' => 'Large (14")', 'price' => 4]
];

$sauces = [
    'tomato' => 'Classic Tomato Sauce',
    'bbq' => 'BBQ Sauce',
    'alfredo' => 'Alfredo Sauce',
    'pesto' => 'Pesto Sauce'
];

$cheese_options = [
    'regular' => 'Regular Cheese',
    'extra' => 'Extra Cheese (+$1.50)',
    'light' => 'Light Cheese'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Your Pizza - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <style>
        .customize-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .pizza-preview {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .pizza-image {
            flex: 1;
            max-width: 400px;
        }

        .pizza-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .pizza-details {
            flex: 1;
        }

        h1, h2, h3, p, span, div {
            font-family: 'Poppins', sans-serif;
        }

        .customization-section {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ff6b00;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .option-card {
            background: #f8f8f8;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-card.selected {
            border-color: #ff6b00;
            background: #fff5f0;
        }

        .option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .option-name {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .option-price {
            color: #ff6b00;
            font-weight: 600;
        }

        .toppings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .topping-card {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f8f8f8;
            border-radius: 4px;
        }

        .topping-card input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .summary-section {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ff6b00;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
        }

        .btn-add-to-cart {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #ff6b00;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-add-to-cart:hover {
            background: #e05f00;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main>
        <div class="customize-container">
            <h1>Customize Your Pizza</h1>
            
            <div class="pizza-preview">
                <div class="pizza-image">
                    <img src="<?php echo $pizza['image_url'] ?: 'https://via.placeholder.com/400x400?text=Pizza'; ?>" 
                         alt="<?php echo htmlspecialchars($pizza['name']); ?>">
                </div>
                <div class="pizza-details">
                    <h2><?php echo htmlspecialchars($pizza['name']); ?></h2>
                    <p><?php echo htmlspecialchars($pizza['description']); ?></p>
                    <p class="base-price">Base Price: <?php echo formatPrice($pizza['price']); ?></p>
                </div>
            </div>

            <form id="customize-form" action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $pizza_id; ?>">
                <input type="hidden" name="is_customized" value="1">

                <div class="customization-section">
                    <h3 class="section-title">Choose Your Crust Size</h3>
                    <div class="options-grid">
                        <?php foreach ($crust_sizes as $size => $details): ?>
                            <div class="option-card" data-size="<?php echo $size; ?>">
                                <div class="option-name"><?php echo $details['name']; ?></div>
                                <div class="option-price"><?php echo $details['price'] > 0 ? '+' . formatPrice($details['price']) : 'No extra charge'; ?></div>
                                <input type="radio" name="crust_size" value="<?php echo $size; ?>" 
                                       <?php echo $size === 'medium' ? 'checked' : ''; ?> style="display: none;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="customization-section">
                    <h3 class="section-title">Choose Your Sauce</h3>
                    <div class="options-grid">
                        <?php foreach ($sauces as $value => $name): ?>
                            <div class="option-card" data-sauce="<?php echo $value; ?>">
                                <div class="option-name"><?php echo $name; ?></div>
                                <input type="radio" name="sauce" value="<?php echo $value; ?>" 
                                       <?php echo $value === 'tomato' ? 'checked' : ''; ?> style="display: none;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="customization-section">
                    <h3 class="section-title">Choose Your Cheese</h3>
                    <div class="options-grid">
                        <?php foreach ($cheese_options as $value => $name): ?>
                            <div class="option-card" data-cheese="<?php echo $value; ?>">
                                <div class="option-name"><?php echo $name; ?></div>
                                <input type="radio" name="cheese" value="<?php echo $value; ?>" 
                                       <?php echo $value === 'regular' ? 'checked' : ''; ?> style="display: none;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="customization-section">
                    <h3 class="section-title">Choose Your Toppings</h3>
                    <div class="toppings-grid">
                        <?php foreach ($toppings as $topping): ?>
                            <div class="topping-card">
                                <input type="checkbox" name="toppings[]" value="<?php echo $topping['id']; ?>" 
                                       data-price="<?php echo $topping['price']; ?>">
                                <label>
                                    <?php echo htmlspecialchars($topping['name']); ?>
                                    <span class="option-price">(+<?php echo formatPrice($topping['price']); ?>)</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="summary-section">
                    <h3 class="section-title">Order Summary</h3>
                    <div id="order-summary">
                        <div class="summary-item">
                            <span>Base Price:</span>
                            <span class="base-price"><?php echo formatPrice($pizza['price']); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Crust Size:</span>
                            <span class="crust-price">$0.00</span>
                        </div>
                        <div class="summary-item">
                            <span>Extra Cheese:</span>
                            <span class="cheese-price">$0.00</span>
                        </div>
                        <div class="summary-item">
                            <span>Toppings:</span>
                            <span class="toppings-price">$0.00</span>
                        </div>
                        <div class="total-price">
                            <span>Total:</span>
                            <span class="final-price"><?php echo formatPrice($pizza['price']); ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-add-to-cart">Add to Cart</button>
                </div>
            </form>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('customize-form');
        const basePrice = <?php echo $pizza['price']; ?>;
        let currentTotal = basePrice;

        // Define crust size prices
        const crustPrices = {
            'small': 0,
            'medium': 2,
            'large': 4
        };

        // Function to update price summary
        function updatePriceSummary() {
            const selectedCrust = document.querySelector('input[name="crust_size"]:checked').value;
            const crustPrice = crustPrices[selectedCrust] || 0;
            const cheesePrice = document.querySelector('input[name="cheese"]:checked').value === 'extra' ? 1.50 : 0;
            
            let toppingsPrice = 0;
            document.querySelectorAll('input[name="toppings[]"]:checked').forEach(checkbox => {
                toppingsPrice += parseFloat(checkbox.dataset.price || 0);
            });

            currentTotal = basePrice + crustPrice + cheesePrice + toppingsPrice;

            // Update summary display
            document.querySelector('.crust-price').textContent = formatPrice(crustPrice);
            document.querySelector('.cheese-price').textContent = formatPrice(cheesePrice);
            document.querySelector('.toppings-price').textContent = formatPrice(toppingsPrice);
            document.querySelector('.final-price').textContent = formatPrice(currentTotal);
        }

        // Format price helper function
        function formatPrice(price) {
            return '$' + price.toFixed(2);
        }

        // Add click handlers for option cards
        document.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    // Remove selected class from siblings
                    this.parentElement.querySelectorAll('.option-card').forEach(sib => {
                        sib.classList.remove('selected');
                    });
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    // Check the radio
                    radio.checked = true;
                    updatePriceSummary();
                }
            });
        });

        // Add change handlers for toppings
        document.querySelectorAll('input[name="toppings[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', updatePriceSummary);
        });

        // Initialize default selections and price summary
        document.querySelector('input[name="crust_size"][value="medium"]').closest('.option-card').classList.add('selected');
        document.querySelector('input[name="sauce"][value="tomato"]').closest('.option-card').classList.add('selected');
        document.querySelector('input[name="cheese"][value="regular"]').closest('.option-card').classList.add('selected');
        updatePriceSummary();

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('customization', JSON.stringify({
                crust_size: document.querySelector('input[name="crust_size"]:checked').value,
                sauce: document.querySelector('input[name="sauce"]:checked').value,
                cheese: document.querySelector('input[name="cheese"]:checked').value,
                toppings: Array.from(document.querySelectorAll('input[name="toppings[]"]:checked')).map(cb => cb.value),
                total_price: currentTotal
            }));

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'cart.php';
                } else {
                    alert(data.message || 'Error adding pizza to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding pizza to cart');
            });
        });
    });
    </script>
</body>
</html> 