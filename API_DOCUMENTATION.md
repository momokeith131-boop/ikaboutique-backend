# IKABOUTIQUE Backend API V2 - Complete Setup Guide

## 🎯 Overview

This is the complete Laravel backend for IKABOUTIQUE V2, a multi-vendor e-commerce platform. It provides RESTful APIs for Flutter mobile apps (iOS & Android).

## 📦 What's Included

### Database Models
- **Users** - Customers, Sellers, Admins
- **Shops** - Vendor stores
- **Products** - Product catalog with images, variations, attributes
- **Categories** - Product categories (hierarchical)
- **Orders** - Customer orders with items
- **Payments** - Payment transactions
- **Carts** - Shopping carts
- **Commissions** - Seller commissions
- **Referrals** - Referral system
- **Notifications** - User notifications & push subscriptions
- **Reviews** - Product reviews
- **Expenses** - Shop accounting

### API Endpoints

#### Authentication (Public)
```
POST   /api/v1/auth/register      - Register new user
POST   /api/v1/auth/login         - User login
POST   /api/v1/auth/logout        - User logout (protected)
GET    /api/v1/auth/me            - Get current user (protected)
POST   /api/v1/auth/refresh       - Refresh JWT token (protected)
```

#### Products
```
GET    /api/v1/products           - List all products (with filters)
GET    /api/v1/products/{id}      - Get product details
POST   /api/v1/products           - Create product (seller only, protected)
PUT    /api/v1/products/{id}      - Update product (protected)
DELETE /api/v1/products/{id}      - Delete product (protected)
```

**Product Filters:**
- `search` - Search by name/description
- `category_id` - Filter by category
- `shop_id` - Filter by shop
- `featured` - Show featured only
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `sort_by` - Sort field (default: created_at)
- `sort_order` - asc or desc (default: desc)
- `per_page` - Items per page (default: 20)

**Example:**
```bash
GET /api/v1/products?category_id=1&min_price=1000&max_price=50000&sort_by=price&sort_order=asc&per_page=20
```

#### Shops
```
GET    /api/v1/shops              - List all shops
GET    /api/v1/shops/{id}         - Get shop details
POST   /api/v1/shops              - Create shop (seller only, protected)
PUT    /api/v1/shops/{id}         - Update shop (protected)
GET    /api/v1/my-shop            - Get current user's shop (protected)
```

#### Categories
```
GET    /api/v1/categories         - Get all categories
GET    /api/v1/categories/{id}    - Get category details with products
```

#### Cart
```
GET    /api/v1/cart               - Get user's cart (protected)
POST   /api/v1/cart/add           - Add item to cart (protected)
PUT    /api/v1/cart/items/{id}    - Update cart item quantity (protected)
DELETE /api/v1/cart/items/{id}    - Remove item from cart (protected)
DELETE /api/v1/cart/clear         - Clear entire cart (protected)
```

#### Orders
```
GET    /api/v1/orders             - List user's orders (protected)
GET    /api/v1/orders/{id}        - Get order details (protected)
POST   /api/v1/orders             - Create order from cart (protected)
PUT    /api/v1/orders/{id}/status - Update order status (seller only, protected)
```

**Order Status:**
- pending
- confirmed
- processing
- shipped
- delivered
- cancelled

#### Payments
```
POST   /api/v1/payments/initiate           - Initiate payment (protected)
POST   /api/v1/payments/{id}/confirm       - Confirm payment (protected)
GET    /api/v1/payments/{id}/status        - Check payment status (protected)
```

**Payment Methods:**
- credit_card (Stripe)
- mobile_money (Orange Money, etc.)
- bank_transfer

#### Notifications
```
GET    /api/v1/notifications              - List notifications (protected)
PUT    /api/v1/notifications/{id}/read    - Mark as read (protected)
POST   /api/v1/notifications/subscribe    - Subscribe to push notifications (protected)
POST   /api/v1/notifications/unsubscribe  - Unsubscribe from notifications (protected)
```

## 🚀 Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ikaboutique_v2
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate JWT Secret
```bash
php artisan jwt:secret
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### 6. Start Development Server
```bash
php artisan serve
```

API will be available at: `http://localhost:8000/api/v1`

## 📝 Example Requests

### Register
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+223123456789",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "customer"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Products
```bash
curl -X GET "http://localhost:8000/api/v1/products?category_id=1&per_page=20"
```

### Add to Cart (Protected)
```bash
curl -X POST http://localhost:8000/api/v1/cart/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

### Create Order (Protected)
```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "shipping_address": {
      "street": "123 Main St",
      "city": "Bamako",
      "country": "Mali",
      "postal_code": "BP 123"
    },
    "notes": "Please handle with care"
  }'
```

## 🔐 Authentication

All protected endpoints require JWT token in header:
```
Authorization: Bearer {token}
```

**Token Details:**
- Algorithm: HS256
- TTL: 60 minutes
- Refresh TTL: 20160 minutes (14 days)

## 🌱 Test Data

After seeding, you can use these credentials:

**Admin:**
- Email: admin@ikaboutique.com
- Password: password123

**Customers:**
- customer1@example.com to customer5@example.com
- Password: password123 (all)

**Sellers:**
- seller1@example.com to seller3@example.com
- Password: password123 (all)

## 📱 Mobile App Integration (Flutter)

The API is ready for Flutter integration. Here's what your Flutter app needs:

1. **Store JWT token** from login response
2. **Include token** in Authorization header for protected routes
3. **Handle 401** responses to refresh token or redirect to login
4. **Parse JSON responses** - all endpoints return standard format:
   ```json
   {
     "message": "Success description",
     "data": { /* response data */ }
   }
   ```

## 🔧 Configuration

### Payment Gateway
Configure in `.env`:
```env
PAYMENT_GATEWAY=stripe
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
```

### File Storage
Configure in `.env`:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=ikaboutique-uploads
```

## 📊 Database Schema

See migrations in `database/migrations/` for complete schema.

## 🐛 Error Handling

All errors follow this format:
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["validation error message"]
  }
}
```

**Common HTTP Status Codes:**
- `200 OK` - Success
- `201 Created` - Resource created
- `400 Bad Request` - Validation error
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Permission denied
- `404 Not Found` - Resource not found
- `409 Conflict` - Resource already exists
- `422 Unprocessable Entity` - Invalid data
- `500 Server Error` - Server error

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Generate JWT secret: `php artisan jwt:secret`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Setup SSL/HTTPS
- [ ] Configure web server (Nginx/Apache)
- [ ] Setup database backups
- [ ] Configure error logging

## 📞 Support

For issues or questions:
- Create an issue on GitHub
- Check existing documentation
- Review API examples

## 📄 License

MIT License - See LICENSE file

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Status:** ✅ Ready for Production
