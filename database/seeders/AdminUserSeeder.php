<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Konto do logowania w panelu Filament (/admin).
     * Hasło jest zdefiniowane w README (środowisko deweloperskie).
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@trs.local'],
            [
                'name' => 'Administrator TRS',
                'password' => Hash::make('TRS_k9Lm#2pQxW'),
            ],
        );
    }
}
