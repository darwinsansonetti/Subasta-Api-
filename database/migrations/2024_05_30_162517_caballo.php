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
        Schema::create('caballo', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250);
            $table->integer('nro_caballo');
            $table->boolean('retirado')->default(0);
            $table->integer('puesto_llegada');
            $table->double('dividendo',  8,  2);
            
            $table->unsignedBigInteger('carrera_id');
 
            $table->foreign('carrera_id')->references('id')->on('carrera')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caballo');
    }
};
