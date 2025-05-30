# Los Pollos Hermanos Pizza Ordering System


## Core System Modules

The online pizza ordering system must have three main modules:

1. **Ordering Module**
   - Login/Sign Up functionality for customers
   - Menu display with all pizzas and non-pizza items with prices
   - "Build Your Own Pizza" customization feature
   - Shopping cart functionality
   - Payment processing (online and cash on delivery options)
   - Delivery/pickup option selection
   - Order tracking

2. **Order Processing Module**
   - Different interfaces for different staff roles:
     - Kitchen staff: View pending orders, update status to "Prepared"
     - Delivery staff: Receive prepared orders, update status during delivery process
     - Counter staff: Handle pickup orders and update their status
   - Role-based access control for different staff functions

3. **Administration Module**
   - Admin/superuser management interface
   - Ingredient management (add/edit/delete)
   - Pizza and non-pizza item management (add/edit/delete)
   - User management (add/edit/delete employees)
   - Order management (add/edit/delete)
   - Admin account functions (login, password reset, profile editing, etc.)
   - Dashboard for admin

## Specific Features Required

### For Customers:
- Account creation with personal details
- View and select from menu items with images and prices
- Customize pizzas (crust size, sauce, cheese options, toppings)
- Real-time price updates during customization
- Add multiple items to cart
- Select delivery or pickup option
- Online payment processing
- Order confirmation
- Order status tracking
- Estimated delivery time display

### For Staff:
- Role-specific interfaces
- Order status management
- Customer notifications at different stages
- Delivery management

### For Administrators:
- Complete system management
- Product/menu management
- User/employee management
- Order management


## Features

- Customer ordering system with menu display and customization
- Staff order processing interface
- Administrative dashboard
- Real-time order tracking
- Online payment processing
- Delivery management system


## Security

- All passwords are hashed using PHP's password_hash()
- SQL injection prevention using prepared statements
- XSS protection implemented
- CSRF protection for forms

## Local Development Setup

### Prerequisites
- XAMPP (or similar package with Apache, MySQL, PHP)
- Web browser
- Git (optional)

### Installation Steps
1. Install XAMPP
2. Start Apache and MySQL services from the XAMPP control panel
3. Clone or extract this repository to `C:\xampp\htdocs\Los Pollos Hermanos` (or your XAMPP htdocs directory)
4. Open phpMyAdmin (http://localhost/phpmyadmin)
5. Create a new database named `los_pollos_hermanos`
6. Import the database schema from `database/schema.sql`

### Running the Website
1. Ensure Apache and MySQL services are running in XAMPP
2. Open your browser and navigate to [http://localhost/Los%20Pollos%20Hermanos](http://localhost/Los%20Pollos%20Hermanos)
3. For admin access, use these default credentials:
   - Username: admin
   - Password: password

### Configuration
- Database settings can be modified in `config/database.php` if needed
- Default database configuration:
  - Host: localhost
  - Username: root
  - Password: (blank)
  - Database: los_pollos_hermanos

