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
        
        <!-- Mobile menu toggle -->
        <div class="mobile-menu-toggle" id="mobileMenuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <div class="nav-menu" id="navMenu">
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
    position: relative;
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

/* Mobile menu toggle (hamburger) */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 5px;
    z-index: 1001;
}

.mobile-menu-toggle span {
    width: 25px;
    height: 3px;
    background-color: #333;
    margin: 3px 0;
    transition: 0.3s;
    border-radius: 2px;
}

/* Hamburger animation */
.mobile-menu-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.mobile-menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.mobile-menu-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

/* Mobile styles */
@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: flex;
    }
    
    .nav-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: #fff;
        flex-direction: column;
        gap: 0;
        padding: 1rem 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease-in-out;
        z-index: 999;
        max-height: 0;
        overflow: hidden;
    }

    .nav-menu.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
        max-height: 500px;
    }

    .nav-link {
        width: 100%;
        padding: 1rem 1.5rem;
        border-radius: 0;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .nav-link:last-child {
        border-bottom: none;
    }

    .nav-link:hover {
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    /* Ensure header doesn't block content on mobile */
    .header {
        position: sticky;
        top: 0;
    }
    
    /* Reduce padding on mobile to save space */
    .navbar {
        padding: 0 1rem;
    }
    
    .header {
        padding: 0.75rem 0;
    }
}

/* Tablet styles */
@media (max-width: 1024px) and (min-width: 769px) {
    .nav-menu {
        gap: 1rem;
    }
    
    .nav-link {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navMenu');

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking on a nav link (mobile)
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                }
            });
        });

        // Close menu when clicking outside (mobile)
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                if (!mobileMenuToggle.contains(event.target) && !navMenu.contains(event.target)) {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                mobileMenuToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }
});
</script> 