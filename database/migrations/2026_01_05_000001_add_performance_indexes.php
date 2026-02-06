<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Performance indexes to optimize queries for high-traffic scenarios.
     * These indexes significantly improve:
     * - Contact search operations
     * - Job queue processing
     * - Share request lookups
     */
    public function up(): void
    {
        // Contacts table indexes
        Schema::table('contacts', function (Blueprint $table) {
            // Index for phone search (used in duplicate checks and lookups)
            $table->index('phone', 'contacts_phone_index');

            // Index for name search
            $table->index('name', 'contacts_name_index');

            // Composite index for user's contact listing with search
            $table->index(['user_id', 'created_at'], 'contacts_user_created_index');
        });

        // Jobs table indexes (critical for queue performance)
        Schema::table('jobs', function (Blueprint $table) {
            // Index for job availability (used by queue workers)
            $table->index('available_at', 'jobs_available_at_index');

            // Composite index for queue worker polling
            $table->index(['queue', 'available_at'], 'jobs_queue_available_index');
        });

        // Share requests indexes
        Schema::table('share_requests', function (Blueprint $table) {
            // Index for recipient's pending requests
            $table->index(['recipient_id', 'status'], 'share_requests_recipient_status_index');

            // Index for sender's requests
            $table->index(['sender_id', 'status'], 'share_requests_sender_status_index');
        });

        // Share request contacts indexes
        Schema::table('share_request_contacts', function (Blueprint $table) {
            // Index for phone lookups
            $table->index('phone', 'share_request_contacts_phone_index');
        });

        // Subscriptions indexes
        Schema::table('subscriptions', function (Blueprint $table) {
            // Index for active subscription lookups
            $table->index(['user_id', 'ends_at'], 'subscriptions_user_ends_index');

            // Index for type filtering
            $table->index('type', 'subscriptions_type_index');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Index for suspension status checks
            $table->index('is_suspended', 'users_suspended_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_phone_index');
            $table->dropIndex('contacts_name_index');
            $table->dropIndex('contacts_user_created_index');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_available_at_index');
            $table->dropIndex('jobs_queue_available_index');
        });

        Schema::table('share_requests', function (Blueprint $table) {
            $table->dropIndex('share_requests_recipient_status_index');
            $table->dropIndex('share_requests_sender_status_index');
        });

        Schema::table('share_request_contacts', function (Blueprint $table) {
            $table->dropIndex('share_request_contacts_phone_index');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_user_ends_index');
            $table->dropIndex('subscriptions_type_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_suspended_index');
        });
    }
};
