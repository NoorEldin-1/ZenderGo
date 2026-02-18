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
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('store_name');
        });

        Schema::table('share_request_contacts', function (Blueprint $table) {
            $table->dropColumn('store_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('store_name')->nullable();
        });

        Schema::table('share_request_contacts', function (Blueprint $table) {
            $table->string('store_name')->nullable();
        });
    }
};
