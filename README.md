<div align="center">

# 👠 Heel Bari — Mobile-First Women's Footwear eCommerce Platform

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-v3%2Fv5-FDAE4B?style=for-the-badge&logo=filament&logoColor=black)](https://filamentphp.com)
[![Livewire](https://img.shields.io/badge/Livewire-v3%2Fv4-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Database](https://img.shields.io/badge/MySQL-8.0%20Strict-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Cache & Queue](https://img.shields.io/badge/Redis-Production-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Currency](https://img.shields.io/badge/Currency-BDT%20(%E0%A7%B3)-22C55E?style=for-the-badge)](https://en.wikipedia.org/wiki/Bangladeshi_taka)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)

<p align="center">
  <strong>An enterprise-grade, mobile-optimized online shopping platform built specifically for the Bangladeshi women's footwear market.</strong>
  <br />
  Engineered with high-concurrency checkout locking, strict server-side price trust, automated Bangladesh courier logistics (Steadfast & Pathao), multi-channel payments (bKash, Nagad, Cards, COD), and an executive Filament administration dashboard.
</p>

[Key Features](#-key-features) •
[Architecture](#-architectural-highlights) •
[Tech Stack](#-technology-stack) •
[Getting Started](#-installation--local-setup) •
[Seed Accounts](#-default-credentials) •
[Configuration](#-environment-configuration) •
[Testing](#-testing--code-quality) •
[Deployment](#-production-deployment) •
[Documentation](#-documentation-index)

---

</div>

## 🌟 Executive Summary

**Heel Bari** (হিল বাড়ি) is a specialized direct-to-consumer (D2C) eCommerce ecosystem designed to solve the challenges of retail footwear in Bangladesh. Women's footwear demands nuanced variant handling (exact European sizing `36–41`, color-specific swatches, arch and heel heights) paired with a frictionless mobile checkout tailored to local customer habits.

The platform guarantees **100% server-side calculation integrity**, preventing price tampering or cart manipulation. It integrates directly with local logistics networks (all 64 districts, divisions, and thanas) and natively supports Cash on Delivery (COD) alongside automated digital payment gateway workflows.

---

## 🚀 Key Features

### 📱 1. Mobile-First Customer Storefront
* **Lightweight & App-Like UX**: Optimized for mobile networks (3G/4G/5G) across Bangladesh with touch-friendly navigation, quick-access bottom navigation bar, and slide-over filter drawers.
* **Smart Catalog & Variant Selection**: Browse block heels, kitten heels, stilettos, mules, festive wedges, and casual flats with instant color swatch switching and real-time size availability badges.
* **Instant Predictive Search**: Debounced live product search with image previews and instant category/price filtering.
* **Self-Hosted WebP Image Pipeline**: Fast loading without external hotlinking dependencies or layout shifts.
* **Public Order Tracker**: Direct tracking via phone number and order number (`/track-order`) without forcing prior account login.

### 🛒 2. High-Integrity Cart & Checkout Engine
* **Zero Client Trust Policy**: Prices, discounts, shipping fees, and totals are **never** accepted from the browser. All computations are resolved strictly on the server against the database.
* **Concurrency & Stock Locking**: Stock availability is validated inside database transactions with pessimistic row-locking (`lockForUpdate()`), preventing overselling during Eid or festive peak flashes.
* **Tiered Bangladesh Delivery Pricing**:
  * **Inside Dhaka City**: `৳ 70` (24–48 hours)
  * **Dhaka Suburbs** (Savar, Gazipur, Keraniganj, Narayanganj): `৳ 100`
  * **Outside Dhaka** (All 63 Districts): `৳ 130` (3–5 business days)
  * **Complimentary Free Shipping**: Configurable threshold (e.g., Free delivery on orders over `৳ 3,000`).
* **Dynamic Coupon System**: Fixed amount or percentage discounts with minimum spend constraints, maximum discount caps, and usage limits.

### 💳 3. Multi-Channel Payment Infrastructure
* **Cash on Delivery (COD)**: Fully supported with anti-fraud address verification.
* **SSLCommerz Hosted Gateway Integration**: Seamlessly accept **bKash, Nagad, Rocket, Upay, Visa, Mastercard, and Internet Banking**.
* **Instant Payment Notification (IPN)**: Asynchronous webhook listener validates transaction hashes and signature authenticity directly from SSLCommerz servers to prevent spoofing.
* **Prepaid Order Incentives**: Configurable automated percentage discount (e.g., Extra 5% OFF) when paying via online prepaid channels.

### 🚚 4. Automated Bangladesh Courier Logistics
* **Dual Provider Architecture**: Pluggable courier driver system supporting **Steadfast Courier** and **Pathao Courier**.
* **Automated Consignment Booking**: One-click parcel consignment creation directly from the order fulfillment dashboard.
* **Real-Time Status Synchronization**: Webhook endpoints (`/api/webhooks/courier/steadfast`, `/api/webhooks/courier/pathao`) listen for transit events (In Transit, Delivered, Partial Delivery, Return) and automatically update order lifecycles and customer status histories.

### 👤 5. Customer Account & Exchange Portal
* **Customer Authentication**: Secure phone/email login and registration with rate-limiting defense.
* **Order Tracking Timeline**: Visual step-by-step progress tracking (`Pending` ➔ `Confirmed` ➔ `Processing` ➔ `Shipped` ➔ `Delivered`).
* **Saved Address Book**: Multi-address management partitioned by Division, District, Thana, and Street Address.
* **Self-Service Exchange Requests**: Customers can submit exchange requests for size or color adjustments directly from their delivered orders.

### ⚡ 6. Filament v3/v5 Administration Panel
* **Comprehensive Dashboard**: Real-time sales metrics, revenue analytics, pending consignments, and inventory restock alerts.
* **Catalog Management**: Deep hierarchy categories, products with rich descriptions, and multi-variant SKUs with stock monitoring.
* **Spatie MediaLibrary**: Effortless image reordering, primary photo toggles, and dual storage sync.
* **Order Processing & Invoicing**: Order status transitions with audit logs, printable A4 and thermal packing slip invoices (`/admin/orders/{id}/invoice`).
* **Marketing & Engagement**: Top promo announcement bar manager, homepage hero banner carousels, and verified customer review moderation.
* **Store Configuration**: Dynamic key-value setting management for delivery rates, payment drivers, contact info, and operational switches.

### 🛡️ 7. Security, SEO & Operational Excellence
* **N+1 Query Elimination**: `Model::preventLazyLoading()` enabled in non-production environments to enforce eager-loading across all relationships.
* **Granular Rate Limiting**: Dedicated rate limiters configured for checkout (`throttle:checkout`), login (`throttle:login`), public tracking (`throttle:tracking`), and catalog search (`throttle:api-search`).
* **SEO Suite**: Automatically generated `/sitemap.xml`, dynamic `/robots.txt`, OpenGraph metadata, and structured schema tags.
* **System Health Check Probe**: Public `/health` probe verifying database connectivity, cache accessibility, and environment status.

---

## 🏗️ Architectural Highlights

```
┌────────────────────────────────────────────────────────────────────────┐
│                        HTTP / Livewire Requests                        │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                         [ Form Request Validation ]
                         [ Rate Limiting & Throttling ]
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        Thin Controllers & Livewire                     │
│               (Storefront, CartPage, CheckoutPage, etc.)               │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                       Dedicated Service Layer                          │
│  ┌──────────────────┐ ┌──────────────────┐ ┌─────────────────────────┐  │
│  │   CartService    │ │   OrderService   │ │     PaymentManager      │  │
│  └──────────────────┘ └──────────────────┘ └─────────────────────────┘  │
│  ┌──────────────────┐ ┌──────────────────┐ ┌─────────────────────────┐  │
│  │  CourierManager  │ │    SmsManager    │ │     CurrencyService     │  │
│  └──────────────────┘ └──────────────────┘ └─────────────────────────┘  │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                    Database Transactions & Locking                     │
│                DB::transaction() + lockForUpdate()                     │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                     MySQL 8.0 (Strict) / Redis                         │
│           DECIMAL(10,2) Money • Soft Deletes • Eager Loading           │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 💻 Technology Stack

| Layer | Technology | Details / Purpose |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel 12.x** | Core MVC, Service Providers, Notifications, Queues |
| **Language Runtime** | **PHP 8.3+** | Strict types, match expressions, readonly properties, enums |
| **Admin Panel** | **Filament v3 / v5** | Rapid, elegant backoffice administration interface |
| **Frontend Reactive**| **Livewire v3 / v4** | Server-rendered reactive UI components (Cart, Checkout) |
| **Client Scripts** | **Alpine.js v3** | Micro-interactions, slide drawers, modals, dropdowns |
| **CSS Engine** | **Tailwind CSS v4** | Mobile-first modern utility-first stylesheet |
| **Database** | **MySQL 8.0** | Strict Mode enabled, full InnoDB engine, `DECIMAL(10,2)` |
| **Cache & Queues** | **Redis** | Background SMS/Email jobs, rate limiting, session storage |
| **Media Handling** | **Spatie MediaLibrary** | Self-hosted WebP product and banner asset pipeline |
| **Payment Gateways** | **SSLCommerz & COD** | Hosted checkout, sandbox mode, IPN callback verification |
| **Courier Logistics**| **Steadfast & Pathao** | Local Bangladesh courier consignment booking and tracking |
| **Testing** | **PHPUnit 11** | Extensive feature test coverage for business-critical paths |

---

## 📁 Project Directory Structure

```text
heel-bari/
├── app/
│   ├── Filament/               # Backoffice Admin Panel
│   │   ├── Pages/              # Custom admin dashboard pages
│   │   ├── Resources/          # Filament CRUD resources (Products, Orders, Coupons, etc.)
│   │   └── Widgets/            # Dashboard analytical & KPI widgets
│   ├── Http/
│   │   ├── Controllers/        # Thin controllers (Storefront, Categories, Invoices)
│   │   ├── Middleware/         # Custom route guards and security headers
│   │   └── Requests/           # Dedicated Form Request validation classes
│   ├── Livewire/               # Full-page and inline reactive components
│   │   ├── Account/            # Customer profile, dashboard, and order views
│   │   ├── Auth/               # Login and registration components
│   │   ├── Cart/               # Cart slide-over and main cart page
│   │   ├── Checkout/           # Multi-step guest/auth checkout with stock reservation
│   │   └── Storefront/         # Product grids, category listings, order tracking
│   ├── Models/                 # Eloquent entities with relationships & scopes
│   ├── Notifications/          # Queued customer notifications (Email & SMS)
│   ├── Policies/               # Granular authorization policies
│   └── Services/               # Core Business Logic Layer
│       ├── CartService.php     # Server-side cart recalculations & coupon processing
│       ├── OrderService.php    # Atomic order creation, status flows, inventory locking
│       ├── Courier/            # Steadfast & Pathao API drivers and manager
│       ├── Payment/            # SSLCommerz & Cash on Delivery gateway implementations
│       └── Sms/                # SMS delivery notification providers
├── config/                     # Framework and integration configurations
├── database/
│   ├── factories/              # Model factories for deterministic test generation
│   ├── migrations/             # Complete relational database schema
│   └── seeders/                # Seeders for categories, products, variants, settings, and users
├── docs/                       # Comprehensive Architecture & Operations Documentation
│   ├── deployment.md           # Zero-downtime Ubuntu VPS production guide
│   ├── erd.md                  # Complete database schema design & Mermaid ERD
│   └── images.md               # Asset specifications, aspect ratios & management guide
├── public/                     # Web server root, icons, robots.txt, build assets
├── resources/
│   ├── css/                    # Tailwind CSS configuration and entrypoints
│   ├── js/                     # Alpine.js and frontend bootstrap
│   └── views/                  # Blade templates, layout wrappers, invoice prints
├── routes/
│   ├── web.php                 # Public storefront, customer portal, IPN callbacks
│   └── console.php             # Artisan scheduled commands and tasks
├── scripts/
│   ├── backup.sh               # Automated MySQL database & media backup script
│   └── deploy.sh               # Atomic zero-downtime VPS deployment script
└── tests/
    └── Feature/                # Feature tests (Cart, Checkout, Locking, Gateways, Policies)
```

---

## 🛠️ System Requirements

Ensure your host environment meets the following baseline requirements:

* **PHP**: `>= 8.3` (with `bcmath`, `curl`, `gd`, `intl`, `mbstring`, `mysql`, `redis`, `xml`, `zip`)
* **Composer**: `^2.2`
* **Node.js**: `>= 18.x` & **NPM**: `>= 9.x`
* **Database**: **MySQL 8.0+**
* **Cache/Broker**: **Redis 7.0+** *(Recommended for queue workers and cache in production)*

---

## ⚡ Installation & Local Setup

Follow these steps to set up Heel Bari on your local development workstation:

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/heel-bari.git
cd heel-bari
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Setup Environment Configuration
```bash
# Copy example environment configuration
cp .env.example .env

# Generate unique application encryption key
php artisan key:generate
```

### 4. Configure Database Credentials
Edit your `.env` file and configure your local MySQL database connection:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=heel_bari
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Run Migrations & Seed Sample Catalog
Execute database migrations and populate the store with realistic categories, products, variants (sizes 36–41), settings, outlets, and demo credentials:
```bash
php artisan migrate:fresh --seed
```

### 6. Create Public Storage Symlink
Link the storage directory so product photos and media uploads are publicly accessible:
```bash
php artisan storage:link
```

### 7. Compile Assets & Launch Development Servers
You can launch both the PHP development server and Vite asset compiler simultaneously:
```bash
# Using the pre-configured Concurrent runner:
npm run dev:all

# OR run in separate terminal tabs:
php artisan serve
npm run dev
```

Visit the application in your browser:
* **Storefront**: [http://127.0.0.1:8000](http://127.0.0.1:8000)
* **Admin Dashboard**: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)
* **Health Check**: [http://127.0.0.1:8000/health](http://127.0.0.1:8000/health)

---

## 🔑 Default Credentials

The database seeder automatically initializes demo accounts for testing and verification:

| Role | Email | Password | Access / Capabilities |
| :--- | :--- | :--- | :--- |
| **Store Admin** | `admin@brand.com` | `password` | Full Filament admin access, catalog, orders, settings, invoices |
| **Support Staff** | `staff@brand.com` | `password` | Order processing, parcel booking, review moderation |
| **Customer (Dhaka)** | `nusrat@gmail.com` | `password` | Customer portal, order history, Dhanmondi shipping address |
| **Customer (Chittagong)** | `farhana@gmail.com` | `password` | Customer portal, outside Dhaka shipping address |

> [!CAUTION]
> Always change default administrative passwords before deploying to a staging or production environment.

---

## ⚙️ Environment Configuration

Key configuration parameters available in `.env`:

```env
# Application & Locale
APP_NAME="Heel Bari"
APP_TIMEZONE="Asia/Dhaka"
APP_LOCALE=en

# Currency Settings (BDT)
APP_CURRENCY=BDT
APP_CURRENCY_SYMBOL="৳"
APP_CURRENCY_NAME="Bangladeshi Taka"
APP_CURRENCY_DECIMALS=2
APP_CURRENCY_THOUSAND_SEPARATOR=","
APP_CURRENCY_DECIMAL_SEPARATOR="."
APP_CURRENCY_SYMBOL_POSITION="before"

# SSLCommerz Payment Gateway
SSLCOMMERZ_STORE_ID="your_store_id"
SSLCOMMERZ_STORE_PASSWORD="your_store_password"
SSLCOMMERZ_SANDBOX=true

# Courier Logistics Configuration
COURIER_DEFAULT_DRIVER=steadfast

# Steadfast Courier Credentials
STEADFAST_API_KEY="your_steadfast_api_key"
STEADFAST_SECRET_KEY="your_steadfast_secret_key"
STEADFAST_BASE_URL="https://portal.steadfast.com.bd/api/v1"

# Pathao Courier Credentials
PATHAO_CLIENT_ID="your_client_id"
PATHAO_CLIENT_SECRET="your_client_secret"
PATHAO_USERNAME="your_username"
PATHAO_PASSWORD="your_password"
PATHAO_STORE_ID="your_store_id"
PATHAO_BASE_URL="https://hermes-api-sandbox.pathao.com"
PATHAO_WEBHOOK_SECRET="your_webhook_secret"
```

---

## 🧪 Testing & Code Quality

The codebase enforces strict test coverage for checkout integrity, payment verifications, and concurrency.

### Running Feature & Unit Tests
```bash
# Run the complete test suite
php artisan test

# Run a specific feature test (e.g. checkout & stock locking)
php artisan test --filter=CheckoutAndStockLockingTest

# Run with coverage report
php artisan test --coverage
```

### Key Test Coverage Highlights
* `CheckoutAndStockLockingTest`: Validates database row-level locking (`lockForUpdate`) and stock decrements under simulated race conditions.
* `SslCommerzPaymentTest`: Tests transaction response validation, IPN listeners, and fraudulent payload rejections.
* `CourierServiceTest`: Asserts consignment request formation and payload parsing for Steadfast and Pathao APIs.
* `CartRulesTest` & `CouponRulesTest`: Verifies subtotal recalculation and promotion application rules purely on the server.
* `AuthorizationPoliciesTest`: Enforces role-based permissions between admins, staff members, and customers.

### Code Style & Formatting
The project adheres to PSR-12 and Laravel coding standards using **Laravel Pint**:
```bash
# Check code formatting
./vendor/bin/pint --test

# Fix formatting automatically
./vendor/bin/pint
```

---

## 🚀 Production Deployment

A complete production deployment architecture is documented in [`docs/deployment.md`](docs/deployment.md).

### High-Level Production Checklist
1. **Zero-Downtime Deployment**: Use the automated release script at [`scripts/deploy.sh`](scripts/deploy.sh) for symlink-swapped releases.
2. **Supervisor Queue Workers**: Run queue workers in daemon mode to handle SMS, transactional emails, and courier webhooks:
   ```ini
   [program:heelbari-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/heel-bari/current/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   user=deployer
   numprocs=4
   redirect_stderr=true
   stdout_logfile=/var/log/supervisor/heelbari-worker.log
   ```
3. **Cron Task Scheduler**: Register Laravel's task scheduler in crontab:
   ```bash
   * * * * * cd /var/www/heel-bari/current && php artisan schedule:run >> /dev/null 2>&1
   ```
4. **Automated Daily Backups**: Schedule the backup script at [`scripts/backup.sh`](scripts/backup.sh) to snapshot MySQL databases and media uploads to offsite storage.
5. **Nginx & SSL**: Enable HTTP/2, Gzip/Brotli compression, Let's Encrypt SSL, and Cloudflare Real IP restoration.

---

## 📚 Documentation Index

For in-depth architectural guides and domain specifications, refer to:

* [📘 Database Entity Relationship Diagram (docs/erd.md)](docs/erd.md) — Complete database schema, foreign key relationships, indexes, and Mermaid diagrams.
* [📗 Production Deployment Guide (docs/deployment.md)](docs/deployment.md) — Server hardening, PHP 8.3-FPM tuning, Nginx setup, Redis separation, and zero-downtime releases.
* [📙 Image Management Guide (docs/images.md)](docs/images.md) — Photography dimensions, aspect ratios, WebP conversion rules, and Filament media swapping.
* [📕 Development Guidelines (AGENTS.md)](AGENTS.md) — Architectural principles, service layer standards, security rules, and code contribution standards.

---

## 🛡️ Security Vulnerabilities

If you discover any security vulnerabilities or critical flaws within Heel Bari, please report them directly via email to the engineering team rather than opening a public issue. All reports are acknowledged promptly and patched as high-priority security advisories.

---

## 📄 License

This software is proprietary and open-sourced under the [MIT License](LICENSE).

---

<div align="center">
  Crafted with care for <strong>Heel Bari Bangladesh</strong> • Built with Laravel, Livewire, Filament & Tailwind CSS.
</div>
