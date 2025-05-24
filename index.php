<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['user_role'] : null;

// Set path variable for header/footer
$isSubDirectory = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Los Pollos Hermanos - Pizza Ordering System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-flex">
                    <div class="hero-text">
                        <h1>Welcome to Los Pollos Hermanos</h1>
                        <p>Experience the finest pizza in town, made with love and the freshest ingredients.</p>
                        <div class="cta-buttons">
                            <a href="modules/ordering/menu.php" class="btn btn-primary">Order Now</a>
                            <?php if (!$isLoggedIn): ?>
                                <a href="modules/auth/register.php" class="btn btn-secondary">Sign Up</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hero-image">
                        <img src="assets/images/Los_Pollos_Hermanos_logo.png" alt="Delicious Pizza">
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2 class="text-center mb-3">Why Choose Us?</h2>
                <br>
                <div class="feature-grid">
                    <div class="feature-card">
                        <i class="fas fa-pizza-slice"></i>
                        <h3>Fresh Ingredients</h3>
                        <p>We use only the freshest ingredients for our pizzas.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-truck"></i>
                        <h3>Fast Delivery</h3>
                        <p>Quick and reliable delivery to your doorstep.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-star"></i>
                        <h3>Best Quality</h3>
                        <p>Consistently high-quality food and service.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="featured-pizzas">
            <div class="container">
                <?php include 'templates/menu.php'; ?>
            </div>
        </section>

        <section class="testimonials">
            <div class="container">
                <h2 class="text-center mb-3">What Our Customers Say</h2>
                <br>
                <div class="testimonial-grid">
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Best pizza in town! The crust is perfect and the toppings are always fresh."</p>
                        <p class="customer">- John D.</p>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Fast delivery and the pizza is always hot and delicious!"</p>
                        <p class="customer">- Sarah M.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-section">
            <div class="container">
                <div class="contact-card">
                    <h2 class="mb-2">Contact Us</h2>
                    <p>Have questions? We're here to help!</p>
                    <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@lospolloshermanos.com</p>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 