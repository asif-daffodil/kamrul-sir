# Heel Bari — Image Management & Specification Guide

This guide outlines the image standards, asset architecture, recommended dimensions, and instructions for managing and replacing images via the Filament Admin Panel for the **Heel Bari** eCommerce platform.

---

## 1. Brand Aesthetics & Image Philosophy

Heel Bari is a premium women's footwear brand tailored specifically for shoppers across Bangladesh. To deliver a fast, luxurious, and mobile-optimized experience:
- **Zero External Hotlinking**: All images are self-hosted within the application (`public/` and `storage/app/public/`). Never load images from external unvetted CDNs or placeholder services (e.g., Unsplash hotlinks, via.placeholder).
- **Format**: All images are standardized in **WebP** for lightweight payload, razor-sharp rendering on Retina/OLED mobile screens, and rapid loading over 3G/4G/5G mobile connections in Bangladesh.
- **Brand Palette**:
  - **Background**: Soft luxury cream (`#FAF7F2` to `#F3EDE2` subtle gradient).
  - **Accent**: Warm champagne bronze (`#C5A059`) and deep bronze (`#8C692D`).
  - **Typography**: Dark charcoal (`#1E293B`) and warm stone (`#78716C`).
  - **Framing**: Refined dual inset borders with corner diamond filigree.

---

## 2. Recommended Image Specifications

When shooting or sourcing professional product and marketing photography, adhere to the following specifications:

| Asset Type | Recommended Dimensions | Aspect Ratio | Target File Size | Purpose & Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Product — Main View** | **1000 × 1000 px** | 1:1 Square | < 250 KB | Primary storefront catalog grid, cart thumbnail, and default product detail view. |
| **Product — Portrait Crop** | **800 × 1000 px** | 4:5 Portrait | < 220 KB | Mobile product detail carousel, editorial lookbook cards, and social share cards. |
| **Product — Side Profile** | **1000 × 1000 px** | 1:1 Square | < 250 KB | Displays heel height, arch contour, ankle strap, and posture profile. |
| **Product — Craft Detail** | **1000 × 1000 px** | 1:1 Square | < 250 KB | Close-up texture showing cloud foam insole, hand-stitching, zari work, or buckle. |
| **Category Card** | **800 × 1000 px** | 4:5 Portrait | < 200 KB | Category navigation grid, drawer filters, and category banner headers. |
| **Hero Banner — Desktop** | **1600 × 600 px** | 8:3 Cinematic | < 350 KB | Homepage top carousel on desktop and tablet landscape viewports. |
| **Hero Banner — Mobile** | **800 × 900 px** | 8:9 Portrait | < 200 KB | Homepage hero banner on smartphones (via `<picture>` responsive tag). |
| **Customer Review Avatar** | **200 × 200 px** | 1:1 Square | < 50 KB | Verified customer reviews on homepage and product review sections. |
| **Brand Logo** | **Original Vector / High-Res PNG** | Variable | < 100 KB | Header, footer, invoices, watermarks (`public/images/logo/`). |

### Image Compression Best Practices:
1. Save photos in **WebP** with **85% quality** (offers 30–50% smaller footprint than equivalent JPEG without perceptible loss of detail).
2. Remove EXIF metadata prior to upload to optimize mobile bandwidth.
3. Keep backgrounds consistent (clean studio white `#FFFFFF`, soft off-white `#F9F9F9`, or warm brand cream `#FAF7F2`).

---

## 3. How to Replace Images in Filament Admin

Placeholders are designed to be swapped effortlessly with real high-resolution photographs as new stock arrives.

### 3.1 Swapping Product Images
1. Log in to the Filament Admin Panel at `/admin`.
2. Navigate to **Catalog > Products** in the sidebar.
3. Click **Edit** on the target product.
4. Scroll to the **Product Images** section:
   - **Upload New Photos**: Click **Add Image** or drop files into the image uploader. Upload your 3 standard views: Main, Side, and Detail.
   - **Set Primary Photo**: Toggle the **Is Primary** switch on the main shot that should appear on product catalog cards and search results.
   - **SEO Alt Text**: Fill in descriptive alt text in English and Bengali keywords (e.g., `Velvet Block Heel Pump Midnight Black - Heel Bari Bangladesh`).
   - **Reorder**: Drag and drop the cards using the drag handle to set the display sequence (`0` for Main, `1` for Side, `2` for Detail).
   - **Remove Placeholder**: Click the trash icon on the generated placeholder image card.
5. Click **Save Changes** at the bottom of the page.

> [!NOTE]
> The application supports dual storage: Filament stores files under `storage/app/public/products`, synchronized automatically with Spatie MediaLibrary's `products` collection.

---

### 3.2 Swapping Category Images
1. In the Admin Panel, navigate to **Catalog > Categories**.
2. Click **Edit** on the category (e.g., *Heels*, *Flats*, *Boston Clogs*).
3. Under the **Category Image** upload field, upload an 800×1000 px portrait photo.
4. Click **Save Changes**. The storefront category grid and navigation drawers immediately reflect the new visual.

---

### 3.3 Swapping Hero Banners
1. In the Admin Panel, navigate to **Marketing > Banners**.
2. Click **Edit** on the hero banner.
3. Update:
   - **Desktop Banner Image**: Upload a 1600×600 px banner.
   - **Mobile Banner Image**: Upload an 800×900 px banner.
4. Ensure **Is Active** is toggled ON.
5. Click **Save Changes**. The storefront `<picture>` element will automatically deliver the desktop banner on widescreen displays and the portrait banner on smartphones.

---

## 4. Programmatic Placeholder Generation

The application includes `App\Services\PlaceholderImageGenerator`, powered by PHP GD with anti-aliased Catmull-Rom spline curves and local typography (`resources/fonts/Georgia.ttf` and `Arial.ttf`).

### Available Methods:
```php
use App\Services\PlaceholderImageGenerator;

$generator = new PlaceholderImageGenerator();

// 1. Generate Product Placeholder (1000x1000 or 800x1000)
$generator->generateProductPlaceholder(
    productName: 'Velvet Block Heel Pump',
    categoryName: 'Block Heels',
    variantType: 'main', // 'main' | 'side' | 'detail'
    width: 1000,
    height: 1000,
    outputPath: public_path('products/custom-heel.webp')
);

// 2. Generate Category Card (800x1000)
$generator->generateCategoryPlaceholder(
    categoryName: 'Boston Clogs',
    description: 'Iconic slip-on clogs with contoured footbeds',
    width: 800,
    height: 1000
);

// 3. Generate Hero Banner (Desktop or Mobile)
$generator->generateBannerPlaceholder(
    title: 'Festive Luxury 2026',
    subtitle: 'Handcrafted footwear for Bangladeshi celebrations',
    badge: 'NEW ARRIVAL',
    width: 1600,
    height: 600,
    isMobile: false
);

// 4. Generate Customer Review Avatar (200x200)
$generator->generateReviewAvatar(
    name: 'Nusrat Jahan',
    size: 200
);
```

### Re-seeding All Placeholders:
To regenerate all placeholder graphics and sync the media library from scratch:
```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=BannerSeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=ReviewSeeder
```
Or run the complete seed suite:
```bash
php artisan migrate:fresh --seed
```
