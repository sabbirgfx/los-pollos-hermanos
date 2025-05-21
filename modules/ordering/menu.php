<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$conn = getDBConnection();

// Fetch categories
try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Fetch products
try {
    $stmt = $conn->query("SELECT p.*, c.name as category_name 
                         FROM products p 
                         JOIN categories c ON p.category_id = c.id 
                         ORDER BY c.name, p.name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}

// Group products by category
$productsByCategory = [];
foreach ($products as $product) {
    $category = $product['category_name'];
    if (!isset($productsByCategory[$category])) {
        $productsByCategory[$category] = [];
    }
    $productsByCategory[$category][] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Los Pollos Hermanos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .menu-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .menu-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .menu-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .menu-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .category-section {
            margin-bottom: 3rem;
        }

        .category-title {
            font-size: 1.8rem;
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
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .product-description {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 600;
            color: #ff6b00;
            margin-bottom: 1rem;
        }

        .add-to-cart {
            width: 100%;
            padding: 0.8rem;
            background: #ff6b00;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .add-to-cart:hover {
            background: #ff8533;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            display: flex;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease-out forwards;
            max-width: 350px;
        }

        .toast.success {
            background-color: #28a745;
            border-left: 5px solid #1e7e34;
        }

        .toast.error {
            background-color: #dc3545;
            border-left: 5px solid #bd2130;
        }

        .toast-icon {
            margin-right: 12px;
            font-size: 20px;
        }

        .toast-content {
            flex-grow: 1;
        }

        .toast-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .toast-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .toast-close:hover {
            opacity: 1;
        }

        .cart-count-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff6b00;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            .products-grid {
                grid-template-columns: 1fr;
            }

            .toast-container {
                left: 10px;
                right: 10px;
                bottom: 10px;
            }

            .toast {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="menu-container">
        <div class="menu-header">
            <h1>Our Menu</h1>
            <p>Discover our delicious selection of pizzas and more</p>
        </div>

        <?php 
        // Set module context flag before including the menu template
        $isModuleContext = true;
        include '../../templates/menu.php'; 
        ?>
    </div>

    <!-- Toast notification container -->
    <div class="toast-container" id="toastContainer"></div>

    <?php include '../../includes/footer.php'; ?>

    <script>
    // Function to show a toast notification
    function showToast(type, title, message, duration = 3000) {
        const toastContainer = document.getElementById('toastContainer');
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        // Toast content
        let iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
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
        
        // Append toast to container
        toastContainer.appendChild(toast);
        
        // Handle close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
        
        // Auto-remove toast after duration
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }
        }, duration);
    }

    // Function to update cart count in header
    function updateCartCount(count) {
        const cartNavLink = document.querySelector('a.nav-link[href*="cart.php"]');
        
        if (cartNavLink) {
            let badge = cartNavLink.querySelector('.cart-count');
            
            if (!badge) {
                cartNavLink.classList.add('cart-count-badge');
                badge = document.createElement('span');
                badge.className = 'cart-count';
                cartNavLink.appendChild(badge);
            }
            
            badge.textContent = count;
            
            // Hide badge if count is 0
            if (count <= 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'flex';
            }
        }
    }

    function addToCart(productId) {
        fetch('../../api/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success toast
                showToast('success', 'Added to Cart', data.message);
                
                // Update cart count in header
                if (data.cart_count) {
                    updateCartCount(data.cart_count);
                }
            } else {
                // Show error toast
                showToast('error', 'Error', data.message || 'Could not add product to cart.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', 'Could not add product to cart. Please try again.');
        });
    }
    
    // Add event listeners to all Order Now buttons
    document.addEventListener('DOMContentLoaded', function() {
        const orderButtons = document.querySelectorAll('.order-btn');
        orderButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get the product ID directly from the data-id attribute
                const productId = parseInt(this.getAttribute('data-id'), 10);
                
                if (!productId || isNaN(productId)) {
                    console.error('Invalid product ID');
                    showToast('error', 'Error', 'Invalid product ID');
                    return;
                }
                
                // Call the addToCart function with the product ID
                addToCart(productId);
            });
        });

        // Initialize cart count
        fetch('../../api/cart/count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    updateCartCount(data.count);
                }
            })
            .catch(error => console.error('Error fetching cart count:', error));
    });
    </script>
</body>
</html> 