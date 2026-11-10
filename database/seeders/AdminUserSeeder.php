<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Buat / perbarui akun administrator panel Filament.
     * Kredensial diambil dari .env (ADMIN_EMAIL, ADMIN_PASSWORD).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@jakasset-mail.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => env('ADMIN_PASSWORD', 'jakadmin123!'),
                'email_verified_at' => now(),
            ],
        );
    }
}
