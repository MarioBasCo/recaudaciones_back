<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cobros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idVehiculo');
            //$table->unsignedBigInteger('idTipoVehiculo');
            $table->integer('ticket_number');
            $table->decimal('valor', 9, 2)->default(0);
            $table->date('fecha')->nullable();
            $table->time('hora')->nullable();
            $table->timestamps();

            $table->foreign('idUsuario')->references('id')->on('users')->onDelete('no action');
            $table->foreign('idVehiculo')->references('id')->on('Vehiculos')->onDelete('no action');
            //$table->foreign('idTipoVehiculo')->references('id')->on('tipo_vehiculos')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros');
    }
};
