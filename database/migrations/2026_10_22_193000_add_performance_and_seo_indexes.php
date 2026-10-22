<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['order_number', 'customer_phone'], 'orders_number_phone_idx');
            $table->index(['customer_phone', 'status'], 'orders_phone_status_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['parent_id', 'is_active', 'sort_order'], 'categories_nav_tree_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured', 'created_at'], 'products_active_featured_idx');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['price', 'is_active'], 'variants_price_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_number_phone_idx');
            $table->dropIndex('orders_phone_status_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_nav_tree_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_featured_idx');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('variants_price_active_idx');
        });
    }
};
