<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('type', ['entree', 'sortie']);
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('origine')->nullable();
            $table->string('motif')->nullable();
            $table->date('date_mouvement');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};