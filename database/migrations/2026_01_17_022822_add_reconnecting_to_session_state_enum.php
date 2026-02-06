<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add 'reconnecting' to session_state enum to support reconnection flow.
     */
    public function up(): void
    {
        // MySQL specific - modify ENUM to add new value
        DB::statement("ALTER TABLE users MODIFY COLUMN session_state ENUM('active', 'sleeping', 'none', 'disconnected', 'reconnecting') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First set any 'reconnecting' values to 'none'
        DB::table('users')->where('session_state', 'reconnecting')->update(['session_state' => 'none']);

        // Then remove 'reconnecting' from ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN session_state ENUM('active', 'sleeping', 'none', 'disconnected') DEFAULT 'none'");
    }
};
