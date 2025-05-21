# Los Pollos Hermanos Pizza Ordering System

A comprehensive web-based pizza ordering system built with PHP and MySQL.


I am planning to build a web program for a pizza store named los pollos hermanos. 

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

## Required Technologies

The assessment specifically mentions the following technologies must be used:

1. **Frontend Technologies**:
   - HTML and CSS
   - JavaScript for client-side functionality

2. **Backend Technologies**:
   - PHP for server-side programming
   - MySQL for database management

3. **Important Notes**:
   - Web frameworks (like CakePHP, CodeIgniter, Laminas Yii, etc.) are explicitly prohibited
   - Database-driven functionality is required
   - Proper database schema creation using MySQL is essential
   - PHP database connectivity must be correctly configured

The system should be designed with user experience in mind, maintaining consistent layouts across pages, proper navigation, and following usability guidelines. The website should separate logic from view and use proper coding practices with minimal comments. I am aiming for clean, vibrant and modern UI for the website. Take inspiration for UI elements from popular pizza franchine stores like dominos, also text contents should be from there as well. I want a vibrant orange theme for UI.


## Features

- Customer ordering system with menu display and customization
- Staff order processing interface
- Administrative dashboard
- Real-time order tracking
- Online payment processing
- Delivery management system

## Technical Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository to your web server directory
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Ensure proper permissions are set for the `uploads` directory
5. Access the system through your web browser

## Directory Structure

```
├── assets/           # Static assets (CSS, JS, images)
├── config/           # Configuration files
├── database/         # Database schema and migrations
├── includes/         # PHP includes and utilities
├── modules/          # Core system modules
│   ├── admin/       # Administration module
│   ├── ordering/    # Ordering module
│   └── processing/  # Order processing module
├── uploads/         # Uploaded files (product images)
└── vendor/          # Third-party dependencies
```

## Security

- All passwords are hashed using PHP's password_hash()
- SQL injection prevention using prepared statements
- XSS protection implemented
- CSRF protection for forms

## License

This project is proprietary and confidential. 