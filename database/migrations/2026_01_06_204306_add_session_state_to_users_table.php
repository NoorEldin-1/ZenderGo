<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds session_state column to track WhatsApp session lifecycle:
     * - active: Session is connected and in use
     * - sleeping: Session intentionally closed to save RAM
     * - none: No session configured yet
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('session_state', ['active', 'sleeping', 'none'])
                ->default('none')
                ->after('whatsapp_token');
        });

        // Set existing users with sessions to 'sleeping' (safer default)
        \DB::table('users')
            ->whereNotNull('whatsapp_session')
            ->update(['session_state' => 'sleeping']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('session_state');
        });
    }
};
