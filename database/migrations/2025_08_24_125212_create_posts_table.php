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
        Schema::create('posts', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Pour pouvoir utiliser les clés étrangères et les transactions
            $table->timestamps();
            $table->bigIncrements('id_post'); // Clé primaire automatiquement créée avec "bigIncrements()".
            // "usigned()" nécessaire pour éventuellement pouvoir définir une clé étrangère sur cette colonne.
            $table->bigInteger('id_categorie')->unsigned();
            $table->bigInteger('id_user')->unsigned();
            $table->string('titre');
            $table->string('description');
            $table->float('prix',8,2);

            $table->foreign('id_categorie')->references('id_categorie')->on('categories');
            $table->foreign('id_user')->references(columns: 'id')->on('users');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
