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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Eliminar la inserción del tema "Genérico" aquí si existe
        // DB::table('themes')->insert([
        //     'name' => 'Genérico',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Asegúrate de que la tabla posts tenga una columna theme_id
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('theme_id')->default(1); // Asigna el tema por defecto
            $table->foreign('theme_id')->references('id')->on('themes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);
            $table->dropColumn('theme_id');
        });

        Schema::dropIfExists('themes');
    }
};