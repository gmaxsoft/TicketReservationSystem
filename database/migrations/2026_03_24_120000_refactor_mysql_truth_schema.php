<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }
            if (! Schema::hasColumn('events', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('price');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'unique_hash') && ! Schema::hasColumn('tickets', 'unique_code')) {
                $table->renameColumn('unique_hash', 'unique_code');
            }
            if (Schema::hasColumn('tickets', 'p24_order_id') && ! Schema::hasColumn('tickets', 'payment_id')) {
                $table->renameColumn('p24_order_id', 'payment_id');
            }
            if (! Schema::hasColumn('tickets', 'price_paid')) {
                $table->decimal('price_paid', 10, 2)->nullable()->after('status');
            }
        });

        $this->expandTicketStatusColumn();

        Schema::table('olx_ads', function (Blueprint $table) {
            if (Schema::hasColumn('olx_ads', 'external_id') && ! Schema::hasColumn('olx_ads', 'olx_external_id')) {
                $table->renameColumn('external_id', 'olx_external_id');
            }
            if (! Schema::hasColumn('olx_ads', 'link')) {
                $table->string('link')->nullable()->after('event_id');
            }
            if (! Schema::hasColumn('olx_ads', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('events', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'unique_code') && ! Schema::hasColumn('tickets', 'unique_hash')) {
                $table->renameColumn('unique_code', 'unique_hash');
            }
            if (Schema::hasColumn('tickets', 'payment_id') && ! Schema::hasColumn('tickets', 'p24_order_id')) {
                $table->renameColumn('payment_id', 'p24_order_id');
            }
            if (Schema::hasColumn('tickets', 'price_paid')) {
                $table->dropColumn('price_paid');
            }
        });

        Schema::table('olx_ads', function (Blueprint $table) {
            if (Schema::hasColumn('olx_ads', 'olx_external_id') && ! Schema::hasColumn('olx_ads', 'external_id')) {
                $table->renameColumn('olx_external_id', 'external_id');
            }
            if (Schema::hasColumn('olx_ads', 'link')) {
                $table->dropColumn('link');
            }
            if (Schema::hasColumn('olx_ads', 'last_sync_at')) {
                $table->dropColumn('last_sync_at');
            }
        });
    }

    private function expandTicketStatusColumn(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('pending','paid','cancelled','used') NOT NULL DEFAULT 'pending'");

            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('status', 32)->default('pending')->change();
            });
        }
    }
};
