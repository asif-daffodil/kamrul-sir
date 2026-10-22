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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('shipping_address_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete();

            // Customer Snapshot at time of order
            $table->string('customer_name');
            $table->string('customer_phone')->index();
            $table->string('customer_email')->nullable();
            $table->string('shipping_division');
            $table->string('shipping_district')->index();
            $table->string('shipping_thana');
            $table->text('shipping_address');

            // Financial Calculations (100% Server Calculated)
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('delivery_charge', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            // Lifecycle and Payment Details
            $table->string('status')->default('pending')->index(); // pending, confirmed, processing, shipped, delivered, cancelled, returned
            $table->string('payment_status')->default('pending')->index(); // pending, paid, failed, refunded
            $table->string('payment_method')->default('cod')->index(); // cod, bkash, nagad, sslcommerz
            $table->string('coupon_code')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
