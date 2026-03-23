<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('p24_order_id')->nullable()->after('hold_expires_at');
            $table->timestamp('checked_in_at')->nullable()->after('p24_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['p24_order_id', 'checked_in_at']);
        });
    }
};
