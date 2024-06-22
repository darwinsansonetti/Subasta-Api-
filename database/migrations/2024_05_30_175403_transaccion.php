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
        Schema::create('transaccion', function (Blueprint $table) {
            $table->id();
            $table->double('monto',  8,  2)->default(0);

            $table->unsignedBigInteger('tipo_transaccion_id');
 
            $table->foreign('tipo_transaccion_id')->references('id')->on('tipo_transaccion')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->integer('pivot_id_jugada')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaccion');
    }
};
