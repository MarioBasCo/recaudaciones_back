<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Persona;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $persona = Persona::create([
            'identificacion' => '2400337651',
            'apellidos' => 'Suarez Ricardo',
            'nombres' => 'Cindy',
            'celular' => '0968772698',
            'direccion' => 'Anconcito'
            // Otros campos de la persona
        ]);

        $user = new User();
        $user->name = 'csuarez';
        $user->email = 'admin@example.com';
        $user->password = bcrypt('123456789');

        $user->assignRole('administrador');

        $persona->user()->save($user);
    }
}
