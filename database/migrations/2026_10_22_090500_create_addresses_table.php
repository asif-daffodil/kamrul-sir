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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('type')->default('shipping'); // shipping, billing
            $table->string('name');
            $table->string('phone');
            $table->string('alternative_phone')->nullable();
            $table->string('division');
            $table->string('district')->index();
            $table->string('thana')->index();
            $table->text('address_line');
            $table->string('postal_code')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
