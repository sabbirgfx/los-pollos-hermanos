<?php
// Menu component for Los Pollos Hermanos Pizza Selection
?>

<div class="menu-section">
    <h2 class="menu-title">Our Signature Pizzas</h2>
    <div class="pizza-grid">
        <div class="pizza-card">
            <img src="assets/images/pizzas/margherita.jpg" alt="Margherita Pizza" class="pizza-image">
            <h3>Classic Margherita</h3>
            <p class="description">Fresh tomatoes, mozzarella, basil, and our signature sauce on a perfectly crispy crust</p>
            <p class="price">$14.99</p>
            <button class="order-btn" data-pizza="margherita">Order Now</button>
        </div>

        <div class="pizza-card">
            <img src="assets/images/pizzas/pepperoni.jpg" alt="Pepperoni Supreme" class="pizza-image">
            <h3>Pepperoni Supreme</h3>
            <p class="description">Loaded with premium pepperoni, extra cheese, and our special herb-infused tomato sauce</p>
            <p class="price">$16.99</p>
            <button class="order-btn" data-pizza="pepperoni">Order Now</button>
        </div>

        <div class="pizza-card">
            <img src="assets/images/pizzas/bbq-chicken.jpg" alt="BBQ Chicken Delight" class="pizza-image">
            <h3>BBQ Chicken Delight</h3>
            <p class="description">Grilled chicken, red onions, and bell peppers, drizzled with our house-made BBQ sauce</p>
            <p class="price">$17.99</p>
            <button class="order-btn" data-pizza="bbq-chicken">Order Now</button>
        </div>

        <div class="pizza-card">
            <img src="assets/images/pizzas/veggie.jpg" alt="Vegetarian Paradise" class="pizza-image">
            <h3>Vegetarian Paradise</h3>
            <p class="description">Fresh mushrooms, bell peppers, olives, onions, and tomatoes with premium mozzarella</p>
            <p class="price">$15.99</p>
            <button class="order-btn" data-pizza="veggie">Order Now</button>
        </div>

        <div class="pizza-card">
            <img src="assets/images/pizzas/meat-lovers.jpg" alt="Meat Lovers Dream" class="pizza-image">
            <h3>Meat Lovers Dream</h3>
            <p class="description">Pepperoni, Italian sausage, bacon, ham, and ground beef for the ultimate meat experience</p>
            <p class="price">$18.99</p>
            <button class="order-btn" data-pizza="meat-lovers">Order Now</button>
        </div>

        <div class="pizza-card">
            <img src="assets/images/pizzas/hawaiian.jpg" alt="Hawaiian Special" class="pizza-image">
            <h3>Hawaiian Special</h3>
            <p class="description">Sweet pineapple chunks, premium ham, and extra cheese for a tropical twist</p>
            <p class="price">$16.99</p>
            <button class="order-btn" data-pizza="hawaiian">Order Now</button>
        </div>
    </div>
</div>

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

@media (max-width: 768px) {
    .pizza-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .menu-title {
        font-size: 2rem;
    }
}
</style> 