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
        Schema::table('carrera', function (Blueprint $table) {
            $table->unsignedBigInteger('hipodromo_id');
 
            $table->foreign('hipodromo_id')->references('id')->on('hipodromo')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrera', function (Blueprint $table) {
            $table->dropColumn('hipodromo_id');
        });
    }
};
