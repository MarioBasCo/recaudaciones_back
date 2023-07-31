<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MenusTableSeeder::class,
            RolesTableSeeder::class,
            UsersSeeder::class,
            TipoVehiculosSeeder::class,
            ClientesSeeder::class,
            LocalesTableSeeder::class
        ]);
    }
}
