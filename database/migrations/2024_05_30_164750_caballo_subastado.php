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
        Schema::create('caballo_subastado', function (Blueprint $table) {
            $table->id();
            $table->double('monto_subastado',  8,  2)->default(0);   

            $table->unsignedBigInteger('subasta_id');
 
            $table->foreign('subasta_id')->references('id')->on('subasta')
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
        Schema::dropIfExists('caballo_subastado');
    }
};
