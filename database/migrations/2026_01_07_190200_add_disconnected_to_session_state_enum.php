<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add 'disconnected' state to session_state enum.
     * This is needed when WhatsApp session becomes invalid and user needs to re-authenticate.
     */
    public function up(): void
    {
        // MySQL requires ALTER TABLE to modify ENUM values
        DB::statement("ALTER TABLE `users` MODIFY `session_state` ENUM('active', 'sleeping', 'none', 'disconnected') NOT NULL DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'disconnected' values to 'none'
        DB::table('users')
            ->where('session_state', 'disconnected')
            ->update(['session_state' => 'none']);

        // Then revert the ENUM
        DB::statement("ALTER TABLE `users` MODIFY `session_state` ENUM('active', 'sleeping', 'none') NOT NULL DEFAULT 'none'");
    }
};
