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
        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->string('fecha_creacion', 250);
            $table->double('monto',  8,  2)->default(0);
            $table->boolean('activo')->default(1);
            
            $table->unsignedBigInteger('tipo_apuesta_id');
 
            $table->foreign('tipo_apuesta_id')->references('id')->on('tipo_apuesta')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->unsignedBigInteger('caballo_id');
 
            $table->foreign('caballo_id')->references('id')->on('caballo')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
 
            $table->foreign('user_id')->references('id')->on('user')
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
        Schema::dropIfExists('ticket');
    }
};
