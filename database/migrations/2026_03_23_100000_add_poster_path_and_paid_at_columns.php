<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('poster_path')->nullable()->after('price');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('poster_path');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
