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
                <div class="testimonial-grid">
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Best pizza in town! The crust is perfect and the toppings are always fresh. I've tried many pizzerias in the area, but Los Pollos Hermanos is definitely my favorite."</p>
                        <p class="customer">- John D. from Albuquerque</p>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Fast delivery and the pizza is always hot and delicious! The online ordering system is so easy to use, and their customer service is outstanding."</p>
                        <p class="customer">- Sarah M. from Phoenix</p>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p>"Their specialty pizzas are incredible! The Pollos Special with its unique blend of spices and perfectly cooked chicken is a must-try for anyone visiting."</p>
                        <p class="customer">- Mike T. from Santa Fe</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-section">
            <div class="container">
                <h2 class="section-title text-center mb-3">Contact Us</h2>
                <div class="contact-flex">
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Call Us</h3>
                                <p>(02) 9876 5432</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email Us</h3>
                                <p>info@lospolloshermanos.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Find Us</h3>
                                <p>123 Pizza Street, Sydney, NSW</p>
                            </div>
                        </div>
                    </div>
                    <div class="contact-hours">
                        <h3>Opening Hours</h3>
                        <div class="hours-item">
                            <span class="day">Monday - Thursday</span>
                            <span class="time">11:00 AM - 10:00 PM</span>
                        </div>
                        <div class="hours-item">
                            <span class="day">Friday - Saturday</span>
                            <span class="time">11:00 AM - 11:00 PM</span>
                        </div>
                        <div class="hours-item">
                            <span class="day">Sunday</span>
                            <span class="time">12:00 PM - 9:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 