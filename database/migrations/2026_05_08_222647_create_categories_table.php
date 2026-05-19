<?php
// FICHIER : database/migrations/2024_01_01_000002_create_categories_table.php
// RÔLE    : Crée la table "categories" → ex: Gard'or, Cash Auto, Accessoires
// RELATION: Un produit appartient à une catégorie (produits.categorie_id → categories.id)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');                          // Nom de la catégorie : "Gard'or", "Cash Auto"
            $table->string('description')->nullable();      // Description optionnelle
            $table->string('couleur', 7)->default('#185FA5'); // Couleur hex pour l'interface
            $table->boolean('is_active')->default(true);   // Afficher ou masquer la catégorie
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};