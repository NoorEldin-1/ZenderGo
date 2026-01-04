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
        Schema::create('share_request_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_request_id')->constrained()->onDelete('cascade');
            // Store contact data directly (snapshot at time of request)
            $table->string('name');
            $table->string('phone');
            $table->string('store_name')->nullable();

            // Index for looking up contacts in a request
            $table->index('share_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_request_contacts');
    }
};
