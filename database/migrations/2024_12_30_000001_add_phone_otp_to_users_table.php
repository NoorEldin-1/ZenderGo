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
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable for phone-based auth
            $table->string('email')->nullable()->change();

            // Make password nullable for OTP-only auth
            $table->string('password')->nullable()->change();

            // Add phone-based authentication fields
            $table->string('phone')->unique()->nullable()->after('email');
            $table->string('otp_code', 4)->nullable()->after('phone');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'otp_code', 'otp_expires_at']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
