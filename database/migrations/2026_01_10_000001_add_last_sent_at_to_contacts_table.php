<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds last_sent_at column to track when a contact was last successfully messaged.
     * - null = Never contacted before
     * - timestamp = The last time a message was successfully sent
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('last_sent_at')->nullable()->after('store_name');

            // Add index for efficient filtering
            $table->index('last_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['last_sent_at']);
            $table->dropColumn('last_sent_at');
        });
    }
};
