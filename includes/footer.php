<?php
$current_year = date('Y');
?>
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Los Pollos Hermanos</h3>
            <p>Delicious pizzas made with love and the finest ingredients.</p>
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="../../modules/ordering/menu.php">Menu</a></li>
                <li><a href="../../modules/ordering/cart.php">Cart</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="../../modules/profile/profile.php">My Profile</a></li>
                    <li><a href="../../modules/ordering/orders.php">My Orders</a></li>
                <?php else: ?>
                    <li><a href="../../modules/auth/login.php">Login</a></li>
                    <li><a href="../../modules/auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Contact Us</h3>
            <ul class="contact-info">
                <li><i class="fas fa-phone"></i> (555) 123-4567</li>
                <li><i class="fas fa-envelope"></i> info@lospolloshermanos.com</li>
                <li><i class="fas fa-map-marker-alt"></i> 123 Pizza Street, Albuquerque, NM</li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo $current_year; ?> Los Pollos Hermanos. All rights reserved.</p>
    </div>
</footer>

<style>
.footer {
    background: #1a1a1a;
    color: #fff;
    padding: 4rem 0 0;
    margin-top: 4rem;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
    }
}

.footer-section h3 {
    color: #ff6b00;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.footer-section p {
    color: #ccc;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-link {
    color: #fff;
    font-size: 1.5rem;
    transition: color 0.3s ease;
}

.social-link:hover {
    color: #ff6b00;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 0.8rem;
}

.footer-links a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #ff6b00;
}

.contact-info {
    list-style: none;
    padding: 0;
}

.contact-info li {
    color: #ccc;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.contact-info i {
    color: #ff6b00;
}

.footer-bottom {
    text-align: center;
    padding: 1.5rem 0;
    margin-top: 3rem;
    border-top: 1px solid #333;
}

.footer-bottom p {
    color: #666;
    font-size: 0.9rem;
}
</style> 