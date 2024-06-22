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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250);
            $table->string('cedula', 250)->unique();
            $table->string('direccion', 250);
            $table->string('fecha_nacimiento', 250);
            $table->string('email', 250)->unique();
            $table->string('phone', 250);
            $table->double('saldo',  8,  2)->default(0);            
            $table->boolean('activo')->default(1);
            $table->string('username', 250)->unique();
            $table->string('password', 250);
            $table->unsignedBigInteger('rol_id');
 
            $table->foreign('rol_id')->references('id')->on('rol')
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
        Schema::dropIfExists('user');
    }
};
