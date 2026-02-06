<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Phone-based auth support
            $table->string('email')->nullable(); // Made nullable
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // Made nullable

            // Added columns
            $table->string('phone')->unique()->nullable();

            // WhatsApp Session
            $table->string('whatsapp_session')->nullable();
            $table->text('whatsapp_token')->nullable();
            $table->enum('session_state', ['active', 'sleeping', 'none', 'disconnected'])->default('none');

            $table->boolean('is_suspended')->default(false);
            $table->enum('suspension_reason', ['security', 'subscription'])->nullable();
            $table->timestamp('suspended_at')->nullable();

            $table->enum('theme_preference', ['light', 'dark'])->default('light');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
