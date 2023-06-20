<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Cliente;
use App\Models\Vehiculo;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $persona = Persona::create([
            'identificacion' => '9999999999',
            'apellidos' => 'Final',
            'nombres' => 'Consumidor',
        ]);

        $cliente = Cliente::create([
            'model' => Persona::class,
            'model_id' => $persona->id,
        ]);

        $cliente->modelo()->associate($persona);
        $cliente->save();

        $vehiculosDefault = [
            [
                'idCliente' => $cliente->id,
                'idTipoVehiculo' => 1,
                'placa' => '111-111',
            ],
            [
                'idCliente' => $cliente->id,
                'idTipoVehiculo' => 2,
                'placa' => '222-222',
            ],
            [
                'idCliente' => $cliente->id,
                'idTipoVehiculo' => 3,
                'placa' => '333-333',
            ]
        ];

        foreach ($vehiculosDefault as $v) {
            Vehiculo::create($v);
        }

    }
}
