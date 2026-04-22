# Ajeer Subscription Platform

A production-grade Laravel 13 API application implementing a comprehensive service subscription and booking system with clean architecture, advanced security features, and enterprise-grade reliability.

## Overview

The Ajeer Subscription Platform is a modern REST API that enables users to subscribe to maintenance services, browse service catalogs, manage shopping carts, and schedule bookings with built-in conflict prevention and subscription management.

## Architecture

### Clean Architecture Pattern

This application follows Domain-Driven Design (DDD) principles with clear separation of concerns:

```
app/
|-- Core/
|   |-- Domain/           # Business logic, enums, domain exceptions
|   |-- Application/      # Services, DTOs, interfaces (use cases)
|   |-- Infrastructure/   # Repository implementations, external services
|-- Modules/
|   |-- User/            # Authentication and user management
|   |-- Service/         # Service catalog management
|   |-- Booking/         # Appointment booking system
|   |-- Cart/            # Shopping cart functionality
|   |-- Package/         # Service bundles and pricing
|   |-- Subscription/    # Subscription lifecycle management
|-- Http/
|   |-- Controllers/     # API endpoint handlers
|   |-- Middleware/      # Cross-cutting concerns (auth, logging, idempotency)
|   |-- Responses/       # Standardized API response format
```

### Database Schema (ERD)

```mermaid
erDiagram
    users ||--o{ bookings : "has many"
    users ||--o{ subscriptions : "has many"
    users ||--|| carts : "has one"
    
    services ||--o{ bookings : "has many"
    services ||--o{ cart_items : "polymorphic"
    services ||--o{ package_services : "belongs to many"
    
    packages ||--o{ cart_items : "polymorphic"
    packages ||--o{ package_services : "belongs to many"
    
    carts ||--o{ cart_items : "has many"
    
    bookings ||--|| booking_status : "enum"
    subscriptions ||--|| subscription_status : "enum"
    subscriptions ||--|| subscription_type : "enum"
    
    users {
        int id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }
    
    services {
        int id PK
        string name
        text description
        decimal price(10,2)
        int duration_minutes
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    packages {
        int id PK
        string name
        text description
        decimal price(10,2)
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    package_services {
        int package_id FK
        int service_id FK
    }
    
    bookings {
        int id PK
        int user_id FK
        int service_id FK
        timestamp scheduled_at
        string status(32)
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    subscriptions {
        int id PK
        int user_id FK
        string type(32)
        string status(32)
        timestamp starts_at
        timestamp ends_at
        timestamp grace_ends_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    carts {
        int id PK
        int user_id FK
        timestamp created_at
        timestamp updated_at
    }
    
    cart_items {
        int id PK
        int cart_id FK
        string item_type(32)
        int item_id
        int quantity
        timestamp created_at
        timestamp updated_at
    }
```

## Tech Stack

- **Framework**: Laravel 13
- **Database**: PostgreSQL 12+
- **Cache**: Redis 6+
- **Authentication**: Laravel Sanctum
- **Testing**: Pest PHP
- **Documentation**: Scribe
- **Logging**: Multi-channel logging with Log Viewer
- **Enums**: omaradel/enum
- **Architecture**: Clean Architecture + DDD

## Requirements

- PHP 8.2+
- PostgreSQL 12+
- Redis 6+
- Composer 2.0+
- Node.js 16+ (for asset compilation)

## Quick Start

### 1. Clone and Install

```bash
git clone <repository-url>
cd subscription
composer install
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration

Update `.env` with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ajeer_subscription
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Database Setup

```bash
# Run migrations with enum conversion and indexes
php artisan migrate

# Seed demo data (creates demo user and sample data)
php artisan db:seed

# Verify demo credentials are printed
```

### 5. Start Development Server

```bash
php artisan serve
npm run dev
```

Visit `http://localhost:8000` for the API and `http://localhost:8000/docs` for API documentation.

## Demo Credentials

After running `php artisan db:seed`, you can use these credentials:

```json
{
  "email": "demo@ajeer.app",
  "password": "password"
}
```

The demo user includes:
- Active trial subscription (30 days)
- 2 sample bookings (1 future confirmed, 1 past completed)
- Cart with service and package items
- Full access to all features

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Response Format

All API responses follow a consistent envelope format:

```json
{
  "data": { ... },
  "meta": {
    "message": "Operation completed successfully",
    "pagination": { ... }
  }
}
```

Error responses:
```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error description"
  }
}
```

### Authentication Flow

#### 1. Register User
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### 2. Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "demo@ajeer.app",
    "password": "password"
  }'
```

#### 3. Use Token
```bash
curl -X GET http://localhost:8000/api/v1/services \
  -H "Authorization: Bearer {your-token}"
```

### Core Endpoints

#### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - User login
- `POST /auth/logout` - User logout
- `GET /auth/me` - Get current user profile

#### Services
- `GET /services` - List active services (paginated)
- `GET /services/{id}` - Get service details

#### Bookings
- `GET /bookings` - List user bookings (paginated)
- `POST /bookings` - Create new booking
- `GET /bookings/{id}` - Get booking details
- `PATCH /bookings/{id}/cancel` - Cancel booking

#### Cart Management
- `GET /cart` - Get user cart with items and total
- `POST /cart/items` - Add item to cart
- `PATCH /cart/items/{id}` - Update cart item quantity
- `DELETE /cart/items/{id}` - Remove item from cart

#### Package Management
- `GET /packages` - List active packages (paginated)
- `GET /packages/{id}` - Get package details with services

#### Subscription Lifecycle
- `GET /subscriptions/current` - Get current subscription
- `POST /subscriptions/start-trial` - Start trial subscription
- `POST /subscriptions/cancel` - Cancel subscription
- `GET /subscriptions/check` - Check subscription status

#### Checkout
- `POST /checkout` - Process cart checkout (fan-out to bookings)

### Idempotency

For POST endpoints, include an `Idempotency-Key` header:
```bash
curl -X POST http://localhost:8000/api/v1/bookings \
  -H "Idempotency-Key: unique-key-123" \
  -H "Authorization: Bearer {token}" \
  -d '{ "service_id": 1, "scheduled_at": "2023-12-01T10:00:00Z" }'
```

### Rate Limiting

Endpoints are rate limited:
- Authentication: 5 requests/minute
- General API: 60 requests/minute
- Booking operations: 10 requests/minute
- Checkout: 5 requests/minute

## Business Rules

### Subscription Management
- **Trial Subscriptions**: 30-day trial with 7-day grace period
- **Active Requirement**: Active subscription required for booking creation
- **Auto-Expiry**: Subscriptions expire automatically after end date
- **Grace Period**: Users can access services during grace period
- **Trial Limits**: One trial per user, no duplicate active subscriptions

### Booking System
- **Overlap Prevention**: No overlapping bookings for same user
- **Future Scheduling**: Booking times must be in the future
- **Cancellation Rules**: Can cancel pending/confirmed bookings only if future
- **Status Flow**: pending -> confirmed -> completed/canceled
- **Confirmation**: Automatic email notifications on booking creation

### Cart Management
- **Item Types**: Services and packages can be added to cart
- **Quantity Limits**: Maximum 10 items per cart item
- **Price Calculation**: Automatic total calculation with package discounts
- **Checkout Flow**: Cart items converted to bookings during checkout
- **Validation**: Service must belong to cart item during checkout

### Package Pricing
- **Discount Requirement**: Package price must be lower than sum of services
- **Automatic Calculation**: Discount percentages calculated automatically
- **Service Validation**: Only active services can be included
- **Bundle Logic**: Packages contain multiple services at discounted rates

### Security & Reliability
- **Rate Limiting**: Tiered limits per endpoint group
- **Idempotency**: POST endpoints support idempotent requests
- **Data Sanitization**: Sensitive data redacted from logs
- **Transaction Safety**: Critical operations in database transactions
- **Soft Deletes**: Core tables support soft deletion

## Testing

### Test Coverage

The application includes comprehensive test coverage:

```bash
# Run all tests
php artisan test

# Run with coverage report
php artisan test --coverage

# Run specific test suites
php artisan test tests/Unit/Services/
php artisan test tests/Feature/
```

### Test Structure

- **Unit Tests**: Service layer business logic
- **Feature Tests**: API endpoint functionality
- **Integration Tests**: Database operations and workflows
- **Test Factories**: Model generation for testing

### Key Test Areas

- Cart service operations
- Checkout flow validation
- Subscription lifecycle management
- Booking conflict prevention
- Authentication and authorization
- Rate limiting and idempotency

### Using Test Data

```php
// Create demo user with subscription
$user = \App\Models\User::factory()->create();
\App\Modules\Subscription\Models\Subscription::factory()->create([
    'user_id' => $user->id,
    'type' => \App\Core\Domain\Enums\SubscriptionType::TRIAL,
]);

// Create services and packages
$services = \App\Modules\Service\Models\Service::factory()->count(3)->create();
$package = \App\Modules\Package\Models\Package::factory()->create();
$package->services()->attach($services->pluck('id'));
```

## Development

### Logging System

Multi-channel logging for better observability:

```bash
# View main application log
tail -f storage/logs/laravel.log

# View module-specific logs
tail -f storage/logs/booking.log
tail -f storage/logs/subscription.log
tail -f storage/logs/api.log
tail -f storage/logs/security.log
```

Access Log Viewer at `http://localhost:8000/log-viewer`.

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Warm up caches
php artisan config:cache
php artisan route:cache
```

### Scheduled Commands

```bash
# View scheduled tasks
php artisan schedule:list

# Run subscription expiry manually
php artisan subscriptions:expire-overdue
```

### Database Operations

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_new_table

# Rollback last migration
php artisan migrate:rollback
```

## Monitoring & Observability

### Health Checks

```bash
# Application health
curl http://localhost:8000/up

# Database connectivity
curl http://localhost:8000/health
```

### Performance Monitoring

- **Request Logging**: All API requests logged with timing
- **Slow Query Detection**: Database queries over 100ms logged
- **Memory Usage**: Peak memory usage tracked per request
- **Error Tracking**: Comprehensive error logging with context

### Security Monitoring

- **Failed Authentication**: Login attempts logged
- **Rate Limit Hits**: Rate limit violations tracked
- **Suspicious Activity**: Anomalous request patterns flagged

## Deployment

### Production Environment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.ajeer.app

DB_CONNECTION=pgsql
DB_SSL_MODE=require

CACHE_STORE=redis
REDIS_CLIENT=phpredis

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Database Setup

```bash
# Production migrations
php artisan migrate --force

# Production seeding
php artisan db:seed --force

# Database optimization
php artisan db:optimize
```

### Queue Configuration

```bash
# Production queue worker
php artisan queue:work --daemon --sleep=3 --tries=3

# Failed job monitoring
php artisan queue:failed
php artisan queue:retry all
```

### Performance Optimization

```bash
# Preload classes
php artisan optimize

# Cache configuration
php artisan config:cache
php artisan route:cache
```

## Roadmap

### Completed Features (v1.0)
- [x] User authentication with Sanctum
- [x] Service catalog management
- [x] Booking system with conflict prevention
- [x] Shopping cart functionality
- [x] Package management with discounts
- [x] Subscription lifecycle management
- [x] Checkout with fan-out to bookings
- [x] Rate limiting and security features
- [x] Comprehensive API documentation
- [x] Multi-channel logging system

### Planned Features (v2.0)
- [ ] Payment gateway integration (Stripe/PayPal)
- [ ] Email notifications (booking reminders, expiry alerts)
- [ ] Advanced scheduling features (recurring bookings)
- [ ] Service provider management
- [ ] Customer reviews and ratings
- [ ] Analytics dashboard
- [ ] Mobile API endpoints
- [ ] Webhook support for integrations
- [ ] Advanced reporting features

### Future Enhancements (v3.0)
- [ ] Multi-tenant support
- [ ] Real-time notifications (WebSocket)
- [ ] Machine learning for scheduling optimization
- [ ] Advanced subscription tiers
- [ ] Service marketplace features
- [ ] API versioning strategy
- [ ] GraphQL support
- [ ] Advanced caching strategies

## Contributing

### Development Workflow

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes with tests
4. Run test suite: `php artisan test`
5. Ensure code quality: `php artisan codesniff`
6. Commit changes: `git commit -m 'feat: add amazing feature'`
7. Push to branch: `git push origin feature/amazing-feature`
8. Open Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Write tests for all new functionality
- Update documentation for API changes
- Use conventional commit messages
- Keep pull requests focused and atomic

## License

This project is open-sourced software licensed under the MIT license.

## Support

For questions and support:

- **Documentation**: Visit `/docs` endpoint for API documentation
- **Issues**: Open an issue on GitHub repository
- **Email**: Contact development team at support@ajeer.app
- **Community**: Join our Discord server for developer discussions

---

**Ajeer Subscription Platform** - Built with Laravel 13, Clean Architecture, and enterprise-grade reliability.
