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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idCliente');
            $table->unsignedBigInteger('idTipoVehiculo');
            $table->string('placa', 20);
            $table->boolean('estado')->default(1);
            $table->timestamps();

            $table->foreign('idCliente')->references('id')->on('clientes')->onDelete('no action');
            $table->foreign('idTipoVehiculo')->references('id')->on('tipo_vehiculos')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
