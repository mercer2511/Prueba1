# Laravel Shopping Cart Project

This project implements a shopping cart solution that allows visitors to add products to their cart and complete the purchase. It's built using Laravel 12 with Vue and Laravel Authentication.

## Project Requirements

### Backend Components
- **Item**: Products with properties: id, name, quantity, price
- **Customer**: Users with properties: first_name, last_name, addresses
- **Address**: Shipping locations with properties: line_1, line_2, city, state, zip
- **Cart**: Can have one customer, one shipping address, and multiple items
- **Tax**: Fixed rate of 16%
- **Shipping**: Mock API integration for shipping rates

### Frontend Components
- Add/remove items from cart
- Customer information form
- Shipping address form with postal code
- Payment processing form

## Implementation Steps

### 1. Database Setup and Models

```bash
# Create models with migrations
php artisan make:model Item -m
php artisan make:model Address -m
php artisan make:model Cart -m
php artisan make:model Order -m
php artisan make:model OrderItem -m
```

Modify the existing User model to include customer information.

### 2. API Controllers

```bash
# Create API controllers
php artisan make:controller API/ItemController --api
php artisan make:controller API/CartController --api
php artisan make:controller API/AddressController --api
php artisan make:controller API/CheckoutController
```

### 3. Create Services

```bash
# Create service classes
php artisan make:provider ShippingServiceProvider
php artisan make:provider TaxServiceProvider
```

### 4. Frontend Setup

```bash
# Create Vue components for shopping cart
# Components needed:
# - ItemList.vue
# - CartComponent.vue
# - AddressForm.vue
# - CheckoutForm.vue
# - PaymentForm.vue
```

### 5. Routes Setup

Configure API routes in `routes/api.php` and web routes in `routes/web.php` for the shopping cart functionality.

### 6. Testing

```bash
# Create tests for models, controllers, and services
php artisan make:test ItemTest
php artisan make:test CartTest
php artisan make:test CheckoutTest
```

## Development Workflow

### Setup Project

```bash
# Clone the repository
git clone https://github.com/mercer2511/Prueba1.git
cd Prueba1

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations and seeders
php artisan migrate
php artisan db:seed

# Compile assets
npm run dev
```

### Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, run Vite for frontend
npm run dev
```

### Testing Payment Integration

Use the following test card details:
- Card Number: 4111 1111 1111 1111
- Card Holder: TEST (for success) or FAIL (for declined)
- Expiry Date: Any date after today (e.g., 12/29)

## Project Structure

- **Models**: `app/Models/`
- **Controllers**: `app/Http/Controllers/`
- **Views/Frontend**: `resources/js/`
- **API Routes**: `routes/api.php`
- **Tests**: `tests/`

## Bonus Features

- [ ] Comprehensive test coverage
- [ ] Split project into backend API and frontend Vue application
- [ ] Connect with payment API integration

## Submission

This repository is already set up as a public GitHub repository at: https://github.com/mercer2511/Prueba1
