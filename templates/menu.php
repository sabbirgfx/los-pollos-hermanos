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

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

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

/* Toast notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

.toast {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease-out;
    min-width: 300px;
}

.toast.success {
    border-left: 4px solid #2ecc71;
}

.toast.error {
    border-left: 4px solid #e74c3c;
}

.toast-icon {
    margin-right: 12px;
    font-size: 1.2rem;
}

.toast.success .toast-icon {
    color: #2ecc71;
}

.toast.error .toast-icon {
    color: #e74c3c;
}

.toast-content {
    flex-grow: 1;
}

.toast-title {
    font-weight: bold;
    margin-bottom: 4px;
}

.toast-message {
    color: #666;
    font-size: 0.9rem;
}

.toast-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 1.2rem;
    padding: 0;
    margin-left: 12px;
}

.toast-close:hover {
    color: #666;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

@media (max-width: 768px) {
    .pizza-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .menu-title {
        font-size: 2rem;
    }

    .toast {
        min-width: auto;
        width: calc(100vw - 40px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all Order Now buttons
    const orderButtons = document.querySelectorAll('.order-btn');
    
    // Add click event listener to each button
    orderButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const productId = this.dataset.id;
            
            try {
                const response = await fetch('<?php echo $basePath; ?>modules/ordering/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                });

                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    showToast('success', 'Success', 'Item added to cart!');
                    
                    // Update cart count if it exists
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cartCount;
                    }
                } else {
                    // Show error message
                    showToast('error', 'Error', data.message || 'Failed to add item to cart');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'Error', 'Failed to add item to cart');
            }
        });
    });

    // Toast notification function
    function showToast(type, title, message) {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            // Create toast container if it doesn't exist
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        // Toast content
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${iconClass}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">&times;</button>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Handle close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
        
        // Auto-remove toast after 3 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }
        }, 3000);
    }
});
</script> 