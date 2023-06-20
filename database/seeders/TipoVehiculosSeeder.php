<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoVehiculosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'detalle' => 'Furgón',
                'valor' => '0.5'
            ],
            [
                'detalle' => 'Camioneta',
                'valor' => '1.5'
            ],
            [
                'detalle' => 'Camión',
                'valor' => '2.5'
            ],
        ];
        
        foreach ($tipos as $tipo) {
            TipoVehiculo::create($tipo);
        }
    }
}
