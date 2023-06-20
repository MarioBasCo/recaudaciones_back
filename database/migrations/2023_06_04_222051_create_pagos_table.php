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
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('arriendo_id')->constrained('arriendos', 'id');
            $table->date('fechaPago');
            $table->foreignId('user_id')->constrained('users', 'id');
            $table->decimal('monto', 8, 2);
            $table->decimal('mora', 8, 2)->nullable();
            $table->string('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
