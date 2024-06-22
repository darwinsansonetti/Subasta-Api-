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
        Schema::table('jugadas', function (Blueprint $table) {
            $table->unsignedBigInteger('hipodromo_id');
 
            $table->foreign('hipodromo_id')->references('id')->on('hipodromo')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->unsignedBigInteger('tipo_apuesta_id');
 
            $table->foreign('tipo_apuesta_id')->references('id')->on('tipo_apuesta')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->boolean('activo')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jugadas', function (Blueprint $table) {
            //
        });
    }
};
