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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('pv', 12, 2)->comment('Point Value for MLM commissions');
            $table->integer('stock')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('discount_percentage')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('category');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};