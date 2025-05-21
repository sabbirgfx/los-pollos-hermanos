<div class="sidebar">
    <div class="sidebar-header">
        <h3>Admin Panel</h3>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-pizza-slice"></i>
                    <span>Product Management</span>
                </a>
            </li>
            <li>
                <a href="ingredients.php" <?php echo basename($_SERVER['PHP_SELF']) == 'ingredients.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-pepper-hot"></i>
                    <span>Ingredient Management</span>
                </a>
            </li>
            <li>
                <a href="categories.php" <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-th-large"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="orders.php" <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-shopping-cart"></i>
                    <span>Order Management</span>
                </a>
            </li>
            <li>
                <a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="../../modules/auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div> 