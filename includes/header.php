<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header">
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../../index.php" class="logo">Los Pollos Hermanos</a>
        </div>
        
        <div class="nav-menu">
            <a href="../../modules/ordering/menu.php" class="nav-link">Menu</a>
            <?php if (isLoggedIn()): ?>
                <a href="../../modules/ordering/cart.php" class="nav-link">Cart</a>
                <a href="../../modules/profile/profile.php" class="nav-link">Profile</a>
                <a href="../../modules/auth/logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="../../modules/auth/login.php" class="nav-link">Login</a>
                <a href="../../modules/auth/register.php" class="nav-link">Register</a>
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
    transition: color 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.nav-link:hover {
    color: #ff6b00;
    background-color: rgba(255, 107, 0, 0.1);
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
</style> 