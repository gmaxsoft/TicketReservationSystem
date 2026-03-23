<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olx_ads', function (Blueprint $table) {
            $table->string('external_id')->nullable()->unique()->after('event_id');
            $table->json('advert_data')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('olx_ads', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'advert_data']);
        });
    }
};
