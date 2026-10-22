# AGENTS.md — Development Guidelines & Instructions

This document defines the strict architecture, coding standards, and operational guidelines for all AI agents and developers working on this codebase.

---

## 1. Project Overview

- **Project**: Mobile-first eCommerce platform for **[BRAND_NAME]** specializing in women's footwear in Bangladesh.
- **Target Audience & Locale**: Bangladesh shoppers. Mobile-first experience is paramount (responsive layouts, touch targets, fast load times, lightweight assets).
- **Currency**: Bangladeshi Taka (**BDT / Tk / ৳**).
- **Core Business Domain**: Product catalogs (sizes, colors, variants), cart & checkout, localized delivery management (districts/areas), order lifecycle, and inventory tracking.

---

## 2. Technology Stack

- **PHP**: `8.3+` (utilize modern PHP features: typed properties, match expressions, enums, readonly classes).
- **Framework**: Latest stable **Laravel** (Laravel 11+).
- **Database**: **MySQL** with strict mode enabled.
- **Frontend**: **Blade** + **Livewire (v3)** + **Alpine.js** + **Tailwind CSS**.
- **Admin Panel**: **Filament (v3)**.
- **Cache, Queue & Session**: **Redis** in production environments.

---

## 3. Core Architectural Rules & Standards

### 3.1 Trust Nothing from the Client
- **Prices, Stock, and Totals**: **NEVER** trust client-submitted prices, discounts, sub-totals, delivery charges, or grand totals.
- Every order and cart operation must fetch the authentic prices, active promotional discounts, delivery fees, and stock availability directly from the database and recalculate totals entirely on the server.

### 3.2 Service Layer & Thin Controllers/Components
- **Keep Controllers and Livewire Components Thin**: They must only handle HTTP/UI request-response cycles, input gathering, and rendering.
- **Service Classes for Business Logic**: All business operations (checkout, order placement, cart calculations, inventory reservation, refund processing) must live inside dedicated Service classes (e.g., `App\Services\OrderService`, `App\Services\CartService`, `App\Services\InventoryService`).

### 3.3 Validation & Authorization
- **Validation**: Always use dedicated **Form Request** classes (`php artisan make:request`) for request validation. Do not inline `$request->validate(...)` in controllers or Livewire components unless handling simple, component-local wire state.
- **Authorization**: Enforce authorization strictly via **Policies** (`php artisan make:policy`) and Gates.

### 3.4 Database Transactions & Data Integrity
- Wrap all state-changing, multi-step database mutations inside `DB::transaction(...)`.
- Crucial for:
  - Placing an order.
  - Decrementing or restocking inventory.
  - Processing payments or refunds.
- Handle deadlocks and race conditions with proper locking (e.g., `lockForUpdate()`) when reserving or verifying stock.

### 3.5 Elimination of N+1 Queries
- Prevent lazy loading in all non-production environments. Ensure this is configured in `AppServiceProvider::boot()`:
  ```php
  use Illuminate\Database\Eloquent\Model;

  public function boot(): void
  {
      Model::preventLazyLoading(! app()->isProduction());
  }
  ```
- Always eager-load relationships using `with()`, `load()`, or Livewire relation queries.

---

## 4. Third-Party Integrations & External APIs

- **No Guessing**: Never assume or guess third-party API schemas, endpoints, request formats, or webhook payloads (e.g., payment gateways like bKash, Nagad, SSLCommerz, or courier/logistics APIs like Steadfast, Pathao, RedX).
- **Official Documentation**: Always refer to the official API documentation.
- **User Consultation**: If documentation is missing, ambiguous, or credentials/endpoints are unclear, stop and ask the user directly before implementing.

---

## 5. Security & Environment Configuration

- **Secrets in `.env` Only**: Never hardcode API keys, secrets, credentials, merchant IDs, or tokens anywhere in the codebase.
- **Keep `.env.example` in Sync**: Whenever a new environment variable or configuration setting is introduced:
  1. Add it to `config/*.php` with appropriate fallback / `env(...)` access.
  2. Add the placeholder / dummy entry to `.env.example` immediately.
- Never commit actual secrets or production `.env` files to version control.

---

## 6. Testing Standards

- **Feature Tests**: Write clean, concise **Feature Tests** (`tests/Feature/`) for every critical user and business flow:
  - Adding items to cart with variant checks.
  - Checkout and server-side total recalculation.
  - Stock validation and race condition handling.
  - Order status transitions and cancellations.
  - Payment gateway webhooks / callbacks.
  - Admin management in Filament.
- Use `RefreshDatabase` and model factories for test isolation.

---

## 7. Code Quality & Workflow Checklist

Before finalizing any task or PR:
- [ ] Are prices and totals calculated 100% on the server?
- [ ] Is business logic contained in Service classes?
- [ ] Are Form Requests and Policies utilized?
- [ ] Are all database operations around order/stock wrapped in transactions?
- [ ] Is lazy loading prevented and N+1 avoided?
- [ ] Is `.env.example` updated with any new variables?
- [ ] Are feature tests written and passing for critical paths?
