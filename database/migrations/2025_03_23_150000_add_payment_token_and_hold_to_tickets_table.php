<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('payment_token', 64)->nullable()->unique()->after('unique_hash');
            $table->timestamp('hold_expires_at')->nullable()->after('payment_token');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['payment_token', 'hold_expires_at']);
        });
    }
};
