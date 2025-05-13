# Laravel 12 Marketplace (Inertia.js + Vue.js)

## Features

- **User Authentication**: Register, login, logout, and email verification.
- **Product Management**: Add, edit, and delete products with images, prices, and stock.
- **Product Categories**: Organize products under customizable categories.
- **Shopping Cart**: Users can add products to cart and update quantities.
- **Checkout System**: Simulated checkout flow with order summary.
- **Order History**: Users can view past purchases.
- **Wishlist**: Save products for later.
- **Vendor Panel**: Vendors can manage their products and view orders.
- **Admin Panel**: Admins can manage users, products, categories, and orders.
- **Responsive Design**: Optimized for desktop and mobile.
- **Built with**: Laravel 12, Inertia.js, Vue.js 3, Tailwind CSS

## Requirements

- PHP 8.1+
- Composer
- Node.js & NPM
- Git
- MySQL or compatible database

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/6arshid/Laravel-12-Inertia-vue-Marketplace.git
cd Laravel-12-Inertia-vue-Marketplace
```

### 2. Install Backend Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Set Up Environment File

```bash
cp .env.example .env
```

Update your `.env` file with correct database and mail settings.

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed the Database (Optional)

```bash
php artisan db:seed
```

### 8. Serve the Application

```bash
php artisan serve
```

Visit:

```
http://localhost:8000
```

### 9. Compile Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

---

## Additional Notes

- Ensure `storage/` and `bootstrap/cache/` directories are writable (`chmod -R 775`).
- Default login credentials (if seeded):

```
Email: admin@admin.com
Password: password
```

- You can use Laravel Telescope or Laravel Debugbar for debugging during development.
- Inertia.js + Vue.js integration is already configured and ready to go.

---

Happy building your marketplace! 🛒🚀
