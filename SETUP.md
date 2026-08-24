# IKABOUTIQUE Backend - Installation & Setup Guide

## Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer
- Git

## Step 1: Clone the Repository

```bash
git clone https://github.com/momokeith131-boop/ikaboutique-backend.git
cd ikaboutique-backend
```

## Step 2: Install Dependencies

```bash
composer install
```

## Step 3: Environment Setup

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ikaboutique_v2
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Step 4: Generate Keys

```bash
php artisan key:generate
php artisan jwt:secret
```

## Step 5: Database Setup

```bash
# Run migrations
php artisan migrate

# Seed with test data
php artisan db:seed
```

## Step 6: Start the Server

```bash
php artisan serve
```

API available at: `http://localhost:8000/api/v1`

## Testing

Use Postman or curl to test endpoints.

Example login:
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@ikaboutique.com",
    "password": "password123"
  }'
```

## Troubleshooting

### Port Already in Use
```bash
php artisan serve --port=8001
```

### Database Connection Error
- Check MySQL is running
- Verify credentials in `.env`
- Check database exists

### JWT Secret Missing
```bash
php artisan jwt:secret
```

## Next Steps

1. Read `API_DOCUMENTATION.md` for all endpoints
2. Check `README.md` for project overview
3. Start integrating with Flutter app

---

For more help, see the main README.md
