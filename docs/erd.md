# Database Entity Relationship Diagram (ERD)

This document provides the complete database schema design and entity relationships for the **[BRAND_NAME]** women's footwear eCommerce platform in Bangladesh.

---

## 1. Visual Entity Relationship Diagram (Mermaid)

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string phone UK
        timestamp email_verified_at
        string password
        string role "admin, staff, customer"
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    categories {
        bigint id PK
        bigint parent_id FK "nullable, self-reference"
        string name
        string slug UK
        text description
        string image
        boolean is_active
        integer sort_order
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    products {
        bigint id PK
        bigint category_id FK "nullable"
        string name
        string slug UK
        text short_description
        longtext description
        decimal base_price "10,2"
        boolean is_featured
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    product_variants {
        bigint id PK
        bigint product_id FK
        string color
        string color_code "hex e.g. #800000"
        string size "e.g. 36, 37, 38, 39, 40"
        string sku UK
        decimal price "10,2"
        decimal sale_price "10,2 nullable"
        integer stock "unsigned"
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    product_images {
        bigint id PK
        bigint product_id FK
        bigint product_variant_id FK "nullable"
        string image_path
        string alt_text
        boolean is_primary
        integer sort_order
        timestamp created_at
        timestamp updated_at
    }

    addresses {
        bigint id PK
        bigint user_id FK
        string type "shipping, billing"
        string name
        string phone
        string alternative_phone
        string division
        string district
        string thana
        text address_line
        string postal_code
        boolean is_default
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    coupons {
        bigint id PK
        string code UK
        string type "fixed, percentage"
        decimal value "10,2"
        decimal min_spend "10,2 nullable"
        decimal max_discount "10,2 nullable"
        integer usage_limit "nullable"
        integer used_count
        timestamp starts_at "nullable"
        timestamp expires_at "nullable"
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    carts {
        bigint id PK
        bigint user_id FK "nullable"
        string session_id "nullable, indexed"
        string coupon_code
        timestamp created_at
        timestamp updated_at
    }

    cart_items {
        bigint id PK
        bigint cart_id FK
        bigint product_variant_id FK
        integer quantity "unsigned"
        timestamp created_at
        timestamp updated_at
    }

    orders {
        bigint id PK
        string order_number UK "e.g. ORD-20261022-0001"
        bigint user_id FK "nullable"
        bigint shipping_address_id FK "nullable"
        string customer_name
        string customer_phone
        string customer_email
        string shipping_division
        string shipping_district
        string shipping_thana
        text shipping_address
        decimal subtotal "10,2"
        decimal discount_amount "10,2"
        decimal delivery_charge "10,2"
        decimal total "10,2"
        string status "pending, confirmed, processing, shipped, delivered, cancelled, returned"
        string payment_status "pending, paid, failed, refunded"
        string payment_method "cod, bkash, nagad, sslcommerz"
        string coupon_code
        text notes
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    order_items {
        bigint id PK
        bigint order_id FK
        bigint product_id FK "nullable"
        bigint product_variant_id FK "nullable"
        string product_name
        json variant_details "snapshot: color, size, sku"
        decimal unit_price "10,2"
        integer quantity "unsigned"
        decimal total_price "10,2"
        timestamp created_at
        timestamp updated_at
    }

    order_status_histories {
        bigint id PK
        bigint order_id FK
        string from_status "nullable"
        string to_status
        bigint changed_by FK "nullable (users.id)"
        text comment
        timestamp created_at
        timestamp updated_at
    }

    reviews {
        bigint id PK
        bigint product_id FK
        bigint user_id FK "nullable"
        integer rating "1 to 5"
        text comment
        boolean is_approved "moderation flag"
        timestamp approved_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    outlets {
        bigint id PK
        string name
        string slug UK
        text address
        string district
        string phone
        text map_link
        string opening_hours
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    exchange_requests {
        bigint id PK
        bigint order_id FK
        bigint order_item_id FK
        bigint user_id FK "nullable"
        bigint requested_variant_id FK "nullable"
        string reason
        string status "pending, approved, rejected, completed"
        text customer_notes
        text admin_notes
        timestamp created_at
        timestamp updated_at
    }

    payments {
        bigint id PK
        bigint order_id FK
        string transaction_id "indexed (e.g. bKash TrxID)"
        string payment_method "cod, bkash, nagad, sslcommerz"
        decimal amount "10,2"
        string currency "BDT"
        string status "pending, successful, failed, refunded"
        json payment_details "gateway payload metadata"
        timestamp paid_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    settings {
        bigint id PK
        string key UK
        text value
        string group "delivery, general, contact, payment"
        string type "string, integer, decimal, boolean, json"
        string description
        timestamp created_at
        timestamp updated_at
    }

    %% Relationships
    categories ||--o{ categories : "parent / children"
    categories ||--o{ products : "categorizes"
    products ||--|{ product_variants : "has variants"
    products ||--o{ product_images : "has images"
    products ||--o{ reviews : "receives"
    products ||--o{ order_items : "referenced in"
    product_variants ||--o{ product_images : "specific variant image"
    product_variants ||--o{ cart_items : "added as"
    product_variants ||--o{ order_items : "purchased as"
    product_variants ||--o{ exchange_requests : "requested variant"

    users ||--o{ addresses : "has saved"
    users ||--o{ orders : "places"
    users ||--o{ carts : "owns"
    users ||--o{ reviews : "authors"
    users ||--o{ exchange_requests : "submits"
    users ||--o{ order_status_histories : "audited by"

    carts ||--|{ cart_items : "contains"

    orders ||--|{ order_items : "contains"
    orders ||--o{ order_status_histories : "tracked by"
    orders ||--o{ payments : "receives"
    orders ||--o{ exchange_requests : "generates"
    order_items ||--o{ exchange_requests : "item exchanged"
    addresses ||--o{ orders : "shipping destination"
```

---

## 2. Table Specifications & Standards

### 2.1 Currency & Financials
- All monetary amounts (`base_price`, `price`, `sale_price`, `subtotal`, `discount_amount`, `delivery_charge`, `total`, `amount`) are strictly defined as **`DECIMAL(10, 2)`** to prevent floating point inaccuracies.
- Currency is normalized to **BDT (Bangladeshi Taka)**.

### 2.2 Slugs & Unique Constraints
- `categories.slug`: Unique index.
- `products.slug`: Unique index.
- `product_variants.sku`: Unique index.
- `outlets.slug`: Unique index.
- `coupons.code`: Unique index.
- `orders.order_number`: Unique index (format `ORD-YYYYMMDD-XXXX`).
- `settings.key`: Unique index.
- `cart_items`: Unique composite key on `[cart_id, product_variant_id]`.

### 2.3 Indexes & Performance Optimization
- **Products**: Indexed on `[category_id, is_active]`, `[is_featured]`.
- **Product Variants**: Indexed on `[product_id, is_active]`, `[product_id, color, size]`.
- **Orders**: Indexed on `[user_id, status]`, `[created_at, status]`, `[customer_phone]`, `[shipping_district]`.
- **Reviews**: Indexed on `[product_id, is_approved, rating]`.
- **Outlets**: Indexed on `[district, is_active]`.

### 2.4 Soft Deletes
Soft deletes (`deleted_at`) are sensible and enabled on:
- `users`
- `categories`
- `products`
- `product_variants`
- `addresses`
- `coupons`
- `orders`
- `outlets`

### 2.5 Localized Bangladesh Context
- **Addresses & Orders**: Partitioned into `division`, `district`, and `thana/upazila` to seamlessly integrate with local logistics APIs (Steadfast, Pathao, RedX, Paperfly).
- **Settings**: Localized defaults for inside Dhaka (`৳ 70`), Dhaka suburbs (`৳ 100`), and outside Dhaka (`৳ 130`).
