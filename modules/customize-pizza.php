<?php
require_once '../config/database.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: menu.php');
    exit();
}

$pizza_id = (int)$_GET['id'];

// Get pizza details
$pizza_query = "SELECT * FROM products WHERE id = ? AND is_available = 1";
$stmt = $conn->prepare($pizza_query);
$stmt->bind_param("i", $pizza_id);
$stmt->execute();
$pizza = $stmt->get_result()->fetch_assoc();

if (!$pizza) {
    header('Location: menu.php');
    exit();
}

// Get all available ingredients
$ingredients_query = "SELECT * FROM ingredients WHERE is_available = 1 ORDER BY name";
$ingredients_result = $conn->query($ingredients_query);

// Get default ingredients for this pizza
$default_ingredients_query = "SELECT i.* FROM ingredients i 
                            JOIN product_ingredients pi ON i.id = pi.ingredient_id 
                            WHERE pi.product_id = ? AND pi.is_default = 1";
$stmt = $conn->prepare($default_ingredients_query);
$stmt->bind_param("i", $pizza_id);
$stmt->execute();
$default_ingredients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$default_ingredient_ids = array_column($default_ingredients, 'id');
?>

<div class="customize-container">
    <div class="pizza-preview">
        <h1>Customize Your Pizza</h1>
        <div class="pizza-base">
            <h2><?php echo htmlspecialchars($pizza['name']); ?></h2>
            <p class="base-price">Base Price: $<?php echo number_format($pizza['price'], 2); ?></p>
        </div>
    </div>

    <form id="customize-form" class="customize-form">
        <input type="hidden" name="pizza_id" value="<?php echo $pizza_id; ?>">
        
        <div class="size-selection">
            <h3>Choose Size</h3>
            <div class="size-options">
                <label>
                    <input type="radio" name="size" value="small" checked>
                    <span>Small (+$0)</span>
                </label>
                <label>
                    <input type="radio" name="size" value="medium">
                    <span>Medium (+$2)</span>
                </label>
                <label>
                    <input type="radio" name="size" value="large">
                    <span>Large (+$4)</span>
                </label>
            </div>
        </div>

        <div class="ingredients-selection">
            <h3>Select Toppings</h3>
            <div class="ingredients-grid">
                <?php while ($ingredient = $ingredients_result->fetch_assoc()): ?>
                    <label class="ingredient-item">
                        <input type="checkbox" 
                               name="ingredients[]" 
                               value="<?php echo $ingredient['id']; ?>"
                               <?php echo in_array($ingredient['id'], $default_ingredient_ids) ? 'checked' : ''; ?>>
                        <span class="ingredient-name"><?php echo htmlspecialchars($ingredient['name']); ?></span>
                        <span class="ingredient-price">+$<?php echo number_format($ingredient['price'], 2); ?></span>
                    </label>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="special-instructions">
            <h3>Special Instructions</h3>
            <textarea name="instructions" placeholder="Any special requests?"></textarea>
        </div>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <div id="price-breakdown">
                <p>Base Price: $<span id="base-price"><?php echo number_format($pizza['price'], 2); ?></span></p>
                <p>Size Upgrade: $<span id="size-upgrade">0.00</span></p>
                <p>Additional Toppings: $<span id="toppings-price">0.00</span></p>
                <p class="total">Total: $<span id="total-price"><?php echo number_format($pizza['price'], 2); ?></span></p>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='menu.php'">Back to Menu</button>
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </div>
    </form>
</div>

<style>
.customize-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}

.pizza-preview {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.customize-form {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.size-selection, .ingredients-selection, .special-instructions, .order-summary {
    margin-bottom: 2rem;
}

.size-options {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.size-options label {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e31837;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.size-options input[type="radio"] {
    display: none;
}

.size-options input[type="radio"]:checked + span {
    color: white;
}

.size-options input[type="radio"]:checked + span::before {
    background-color: #e31837;
}

.ingredients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.ingredient-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
}

.ingredient-item:hover {
    background-color: #f5f5f5;
}

.ingredient-name {
    flex: 1;
}

.ingredient-price {
    color: #e31837;
    font-weight: bold;
}

.special-instructions textarea {
    width: 100%;
    height: 100px;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    resize: vertical;
}

.order-summary {
    background-color: #f9f9f9;
    padding: 1rem;
    border-radius: 5px;
}

.total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #e31837;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #ddd;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #e31837;
    color: white;
    border: none;
}

.btn-secondary {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn:hover {
    opacity: 0.9;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('customize-form');
    const basePrice = parseFloat(document.getElementById('base-price').textContent);
    const sizeUpgrade = document.getElementById('size-upgrade');
    const toppingsPrice = document.getElementById('toppings-price');
    const totalPrice = document.getElementById('total-price');

    function updatePrice() {
        let total = basePrice;
        
        // Add size upgrade cost
        const selectedSize = document.querySelector('input[name="size"]:checked').value;
        const sizeCost = selectedSize === 'medium' ? 2 : selectedSize === 'large' ? 4 : 0;
        total += sizeCost;
        sizeUpgrade.textContent = sizeCost.toFixed(2);

        // Add toppings cost
        const selectedToppings = document.querySelectorAll('input[name="ingredients[]"]:checked');
        let toppingsCost = 0;
        selectedToppings.forEach(topping => {
            const price = parseFloat(topping.closest('.ingredient-item').querySelector('.ingredient-price').textContent.replace('+$', ''));
            toppingsCost += price;
        });
        total += toppingsCost;
        toppingsPrice.textContent = toppingsCost.toFixed(2);

        // Update total
        totalPrice.textContent = total.toFixed(2);
    }

    // Add event listeners for price updates
    document.querySelectorAll('input[name="size"]').forEach(input => {
        input.addEventListener('change', updatePrice);
    });

    document.querySelectorAll('input[name="ingredients[]"]').forEach(input => {
        input.addEventListener('change', updatePrice);
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const selectedToppings = Array.from(formData.getAll('ingredients[]'));
        
        // Create cart item object
        const cartItem = {
            pizzaId: formData.get('pizza_id'),
            size: formData.get('size'),
            toppings: selectedToppings,
            instructions: formData.get('instructions'),
            price: parseFloat(totalPrice.textContent)
        };

        // Add to cart logic will be implemented here
        console.log('Adding to cart:', cartItem);
        alert('Pizza added to cart!');
    });

    // Initial price calculation
    updatePrice();
});
</script>

<?php require_once '../includes/footer.php'; ?> 