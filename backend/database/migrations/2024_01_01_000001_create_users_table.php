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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('referral_code')->unique();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->unsignedBigInteger('rank_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('sponsor_id');
            $table->index('rank_id');
            $table->index('status');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('sponsor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rank_id')->references('id')->on('ranks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};