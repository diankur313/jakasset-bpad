<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            InstansiSeeder::class,
            WarnaKendaraanSeeder::class,
        ]);

        // Data dummy hanya untuk pengembangan lokal.
        if (app()->environment('local')) {
            $this->call([
                PegawaiSeeder::class,
                MasterKendaraanSeeder::class,
            ]);
        }
    }
}
