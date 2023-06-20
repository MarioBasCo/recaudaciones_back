<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Local;

class LocalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Local::truncate();

        Local::create([
            'codigo' => 'L1',
            'detalle' => 'Local 1',
        ]);

        Local::create([
            'codigo' => 'L2',
            'detalle' => 'Local 2',
        ]);

        Local::create([
            'codigo' => 'L3',
            'detalle' => 'Local 3',
        ]);
    }
}
