# Simple E-commerce System

A simple multi-role e-commerce web application built with Laravel. The system allows customers to browse products, manage a shopping cart, place orders, and track their purchases. Sellers can manage products, stock, categories, customers, and orders, while administrators control system settings and seller permissions.


## Main Features

- Public product catalogue and product details
- Product search, filtering, sorting, and category browsing
- Customer registration, login, email verification, and password reset
- Role-based access for administrators, sellers, and customers
- Shopping cart and checkout
- Customer order history and order management
- Product, stock, category, and media management
- Seller dashboard with sales and stock statistics
- Customer and profile management
- Product ratings and product-view tracking
- Seller notifications
- Editable header, footer, email, and general site settings
- Seller setting permissions managed by an administrator
- Responsive user interface

## User Roles

### Administrator

The administrator manages system-level settings and permissions.

- Access the admin dashboard
- Manage header and footer content
- Configure email settings
- Manage seller setting permissions
- Manage customers
- Update the admin profile

### Seller

The seller manages the daily activities of the store.

- View sales, revenue, stock, and order statistics
- Create, view, update, and delete products
- Upload product images and videos
- Select a product's primary media
- Mark products as advertised
- Manage product stock and prices
- Create and manage categories
- View and manage customers
- View orders and update their status
- Receive and manage notifications
- Update allowed website settings
- Update the seller profile

### Customer

The customer uses the storefront to find and order products.

- Register and verify an email address
- Log in, log out, and reset a password
- Browse and search products
- Browse products by category
- View product details and media
- Rate products
- Add products to the shopping cart
- Update quantities or remove cart items
- Complete checkout
- View order history and order details
- Cancel eligible orders
- Update or remove eligible order items
- View dashboard statistics
- Manage profile details, password, and profile photo
- View saved address information

## Important System Modules

### 1. Authentication and Authorization

Handles registration, login, logout, password reset, email verification, and role-based route protection.

Important parts:

- `AuthController`
- `PasswordResetController`
- `RoleMiddleware`
- `User` model

### 2. Product Catalogue

Displays store products and provides searching, filtering, sorting, category pages, product details, advertised products, and pagination.

Important parts:

- `ShopController`
- `Product` model
- `ProductDescription` model
- Shop and product Blade views

### 3. Product Management

Allows sellers to create and maintain products, prices, discounts, stock, descriptions, and advertised status.

Important parts:

- `seller/ProductController`
- Product forms and product list views
- Product stock management

### 4. Product Media

Manages product images and videos, including uploads, deletion, and primary-media selection.

Important parts:

- `ProductMedia` model
- Product media upload routes
- Product media management page
- Laravel public storage

### 5. Category Management

Organizes products into categories and allows sellers to create, edit, view, and delete categories.

Important parts:

- `seller/CategoryController`
- `Category` model
- `CategorySeeder`

### 6. Shopping Cart

Allows customers to add products, change quantities, remove products, clear the cart, and view the current cart count.

Important parts:

- `Shop/CartController`
- `Cart` model
- `CartItem` model
- Cart API count endpoint

### 7. Checkout

Validates customer information and stock availability before creating an order. It can also save customer checkout information for future purchases.

Important parts:

- `Shop/CheckoutController`
- `CheckoutInformation` model
- Checkout page
- Database transaction for order creation

The current checkout creates an order directly in the system. An external online payment gateway is not included.

### 8. Order Management

Manages customer orders and the seller order workflow.

Supported order statuses:

- `pending`
- `confirmed`
- `completed`
- `cancelled`

Important parts:

- `Order` model
- `OrderItem` model
- `OrderAddress` model
- `Shop/OrderController`
- `CustomerController`
- `seller/OrderController`

Stock is deducted when an order is confirmed and restored when an eligible confirmed order is cancelled.

### 9. Customer Dashboard

Shows customer order totals, completed and cancelled orders, spending statistics, recent orders, and recent activity.

Important parts:

- `customer/DashboardController`
- Customer dashboard view

### 10. Seller Dashboard and Reports

Provides a summary of store performance.

Dashboard information includes:

- Total and active products
- Total available stock
- Advertised products
- Pending and completed orders
- Completed-order revenue
- Recent orders
- Top-selling products
- Monthly revenue trend
- Low-stock products
- Stock-level charts
- Product distribution by category

Important parts:

- `seller/DashboardController`
- Seller dashboard charts

### 11. Customer Management

Allows sellers and administrators to create, view, edit, search, and delete customer accounts.

Important parts:

- `seller/CustomerController`
- Customer management views

### 12. Profile Management

Allows customers, sellers, and administrators to update their account information.

Supported profile actions include:

- Update name, email, and phone number
- Change password
- Upload a customer profile photo

Important parts:

- `customer/ProfileController`
- `seller/ProfileController`

### 13. Ratings and Product Activity

Customers can rate products, while product views and viewing activity can be recorded for shop analytics and recommendations.

Important parts:

- `Rating` model
- `ProductView` model
- Product rating and view-activity routes

### 14. Notification Module

Provides seller notifications, unread counters, recent notification polling, read status, and notification deletion.

Important parts:

- `Notification` model
- `seller/NotificationController`
- Notification API endpoints

### 15. Website Settings

Stores editable website configuration in the database.

Configurable areas include:

- Header content and images
- Footer brand and contact information
- Social media links
- Email configuration
- Website theme values

Important parts:

- `SiteSetting` model
- `Admin/SiteSettingController`
- Header, footer, and mail setting pages

Footer settings support partial updates, so changing one field does not reset the other saved fields.

### 16. Seller Setting Permissions

Allows an administrator to decide which website settings sellers can view, create, update, or delete.

Important parts:

- `SellerSettingPermission` model
- `Admin/SellerSettingPermissionController`

### 17. Public Pages

Includes public storefront and information pages.

- Shop
- Product details
- Categories
- About
- Contact

Important parts:

- `PageController`
- `PageContent` model

## Main Database Entities

- Users
- Products
- Product descriptions
- Product media
- Product views
- Categories
- Ratings
- Carts
- Cart items
- Checkout information
- Orders
- Order items
- Addresses
- Order addresses
- Notifications
- Site settings
- Seller setting permissions
- Page contents

## Technology Stack

- PHP 8.2 or later
- Laravel 12
- MySQL
- Blade templates
- Bootstrap 5
- Bootstrap Icons
- JavaScript and AJAX
- Vite 7
- Tailwind CSS 4 tooling
- PHPUnit 11

## Project Structure

```text
app/
├── Http/Controllers/    Application controllers
├── Http/Middleware/     Role and request middleware
├── Models/              Eloquent models
└── Support/             Shared helper classes and functions

database/
├── factories/           Model factories
├── migrations/          Database structure
└── seeders/             Initial users and categories

public/
├── css/                 Compiled and page-specific styles
├── img/                 Public images and setting uploads
└── js/                  Frontend JavaScript

resources/
├── css/                 Vite CSS entry files
├── js/                  Vite JavaScript entry files
└── views/               Blade templates

routes/
├── web.php              Public and authentication routes
├── shop.php             Cart, checkout, and customer order routes
├── customer.php         Customer dashboard and account routes
├── seller.php           Seller management routes
└── admin.php            Administrator routes

tests/
├── Feature/             Application feature tests
└── Unit/                Unit tests
```

## Installation

### Requirements

Install the following before running the project:

- PHP 8.2+
- Composer
- MySQL
- Node.js and npm

### Setup

Clone or copy the project, open a terminal in the project directory, and run:

```bash
composer install
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Create a MySQL database, then update these values in `.env`:

```env
APP_NAME="Simple E-commerce"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kids_shop
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed the database:

```bash
php artisan migrate --seed
```

Create the public storage link:

```bash
php artisan storage:link
```

Build frontend assets:

```bash
npm run build
```

Start the application:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

For development with automatic asset rebuilding, run:

```bash
composer run dev
```

## Running Tests

Run all automated tests:

```bash
php artisan test
```

## Email Configuration

The default `.env.example` uses the log mail driver:

```env
MAIL_MAILER=log
```

For real email verification and password-reset messages, configure SMTP values in `.env` or use the administrator mail-settings page.

## Security Notes

- Change all seeded account passwords before deploying.
- Set `APP_DEBUG=false` in production.
- Use secure production database and SMTP credentials.
- Configure the web server to serve the `public` directory.
- Run Laravel optimization commands when deploying.
- Do not commit the `.env` file to source control.

