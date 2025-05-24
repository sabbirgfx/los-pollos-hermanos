<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set path variable for header/footer
$isSubDirectory = true;

// Initialize database connection
$conn = getDBConnection();

// Get all categories with their products
try {
    // Get unique categories with a more specific query
    $stmt = $conn->query("
        SELECT DISTINCT c.id, c.name 
        FROM categories c 
        INNER JOIN products p ON c.id = p.category_id 
        WHERE p.is_available = 1 
        ORDER BY c.name
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Use a different statement handle for products
    $productStmt = $conn->prepare("
        SELECT * 
        FROM products 
        WHERE category_id = ? 
        AND is_available = 1 
        ORDER BY name
    ");
    
    // Fetch products for each category
    foreach ($categories as $i => $category) {
        $productStmt->execute([$category['id']]);
        $categories[$i]['products'] = $productStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Error fetching menu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            font-family: 'Poppins', sans-serif;
        }

        .category-section {
            margin-bottom: 3rem;
        }

        .category-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ff6b00;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #f5f5f5;
        }

        .product-details {
            padding: 1.5rem;
        }

        .product-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: #ff6b00;
            margin-bottom: 1rem;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: #ff6b00;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #e05f00;
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background-color: #e9e9e9;
        }

        .notification-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            max-width: 350px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .alert-success {
            background-color: #4CAF50;
            color: white;
            border-left: 5px solid #388E3C;
        }

        .alert-error {
            background-color: #f44336;
            color: white;
            border-left: 5px solid #d32f2f;
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

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <main>
        <div class="menu-container">
            <h1>Our Menu</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php foreach ($categories as $category): ?>
                <section class="category-section">
                    <h2 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h2>
                    
                    <div class="products-grid">
                        <?php foreach ($category['products'] as $product): ?>
                            <div class="product-card">
                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x200?text=Pizza'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image">
                                
                                <div class="product-details">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                                    
                                    <div class="product-actions">
                                        <?php if ($category['name'] === 'Pizzas'): ?>
                                            <a href="customize-pizza.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                                Customize
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-primary add-to-cart" 
                                                    data-id="<?php echo $product['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                    data-price="<?php echo $product['price']; ?>">
                                                Add to Cart
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <div id="notification-container" class="notification-container"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Notification function
        function showNotification(message, type) {
            const container = document.getElementById('notification-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            container.appendChild(alert);

            // Remove notification after 3 seconds
            setTimeout(() => {
                alert.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    container.removeChild(alert);
                }, 300);
            }, 3000);
        }

        // Add to cart functionality
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = parseFloat(this.dataset.price);
                
                console.log('Attempting to add to cart:', {
                    product_id: productId,
                    name: productName,
                    price: productPrice
                });

                // Create form data
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', '1');

                try {
                    const response = await fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    });

                    // Log the response for debugging
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Response text:', text);
                        throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                    }

                    const data = await response.json();
                    if (data.success) {
                        showNotification(`${productName} added to cart!`, 'success');
                        // Update cart count if available
                        const cartCountElement = document.querySelector('.cart-count');
                        if (cartCountElement && data.cartCount) {
                            cartCountElement.textContent = data.cartCount;
                        }
                    } else {
                        console.error('Server error:', data);
                        showNotification(data.message || 'Error adding item to cart', 'error');
                    }
                } catch (error) {
                    console.error('Error details:', error);
                    showNotification('Error adding item to cart', 'error');
                }
            });
        });
    });
    </script>
</body>
</html> 