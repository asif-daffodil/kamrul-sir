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
            $table->string('courier_name')->nullable()->after('payment_method')->index();
            $table->string('courier_tracking_code')->nullable()->after('courier_name')->index();
            $table->string('courier_consignment_id')->nullable()->after('courier_tracking_code')->index();
            $table->string('courier_status')->nullable()->after('courier_consignment_id')->index();
            $table->json('courier_response')->nullable()->after('courier_status');
            $table->timestamp('courier_sent_at')->nullable()->after('courier_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'courier_name',
                'courier_tracking_code',
                'courier_consignment_id',
                'courier_status',
                'courier_response',
                'courier_sent_at',
            ]);
        });
    }
};
