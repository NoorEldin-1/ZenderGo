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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values
        DB::table('system_settings')->insert([
            ['key' => 'trial_days', 'value' => '7', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'subscription_price', 'value' => '100', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'instapay_number', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
