<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if we're at the root level or in a subdirectory
$isRoot = !isset($isSubDirectory) || $isSubDirectory === false;
$basePath = $isRoot ? '' : '../../';
?>
<header class="header">
    <nav class="navbar">
        <div class="nav-brand">
            <a href="<?php echo $basePath; ?>index.php" class="logo">Los Pollos Hermanos</a>
        </div>
        
        <div class="nav-menu">
            <a href="<?php echo $basePath; ?>modules/ordering/menu.php" class="nav-link">Menu</a>
            <?php if (isLoggedIn()): ?>
                <?php if (hasRole('admin')): ?>
                    <a href="<?php echo $basePath; ?>modules/admin/dashboard.php" class="nav-link">Admin Dashboard</a>
                <?php endif; ?>
                <?php if (hasRole('kitchen_staff')): ?>
                    <a href="<?php echo $basePath; ?>modules/kitchen/orders.php" class="nav-link">Kitchen Dashboard</a>
                <?php endif; ?>
                <?php if (hasRole('delivery_staff')): ?>
                    <a href="<?php echo $basePath; ?>modules/delivery/orders.php" class="nav-link">Delivery Dashboard</a>
                <?php endif; ?>
                <?php if (hasRole('counter_staff')): ?>
                    <a href="<?php echo $basePath; ?>modules/counter/orders.php" class="nav-link">Counter Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo $basePath; ?>modules/ordering/cart.php" class="nav-link">
                    Cart
                    <?php if (isset($_SESSION['cart'])): ?>
                        <span class="cart-count"><?php 
                            $count = 0;
                            foreach ($_SESSION['cart'] as $item) {
                                $count += $item['quantity'];
                            }
                            echo $count;
                        ?></span>
                    <?php else: ?>
                        <span class="cart-count">0</span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo $basePath; ?>modules/profile/profile.php" class="nav-link">Profile</a>
                <a href="<?php echo $basePath; ?>modules/auth/logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="<?php echo $basePath; ?>modules/auth/login.php" class="nav-link">Login</a>
                <a href="<?php echo $basePath; ?>modules/auth/register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<style>
.header {
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    font-family: 'Poppins', sans-serif;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.nav-brand .logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #ff6b00;
    text-decoration: none;
    transition: color 0.3s ease;
}

.nav-brand .logo:hover {
    color: #ff8533;
}

.nav-menu {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.nav-link {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.nav-link:hover {
    color: #ff6b00;
    background-color: rgba(255, 107, 0, 0.1);
}

/* Dashboard link styles */
.nav-link[href*="dashboard.php"],
.nav-link[href*="orders.php"] {
    background-color: rgba(255, 107, 0, 0.1);
    color: #ff6b00;
    font-weight: 600;
}

.nav-link[href*="dashboard.php"]:hover,
.nav-link[href*="orders.php"]:hover {
    background-color: rgba(255, 107, 0, 0.2);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }
    
    .nav-menu {
        flex-direction: column;
        gap: 1rem;
        width: 100%;
        text-align: center;
    }

    .nav-link {
        width: 100%;
        padding: 0.75rem;
    }
}

.cart-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #ff6b00;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    font-size: 0.8rem;
    margin-left: 5px;
}
</style> 