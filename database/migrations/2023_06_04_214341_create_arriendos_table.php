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
        Schema::create('arriendos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('local_id');
            $table->foreignId('persona_id')->constrained('personas', 'id');
            $table->decimal('valorArriendo', 8, 2);
            $table->date('fecha');
            $table->integer('meses');
            $table->boolean('estado')->default(1);
            $table->timestamps();

            $table->foreign('local_id')->references('id')->on('locales')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arriendos');
    }
};
