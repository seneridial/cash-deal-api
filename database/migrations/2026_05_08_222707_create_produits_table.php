<?php
// FICHIER : database/migrations/2024_01_01_000003_create_produits_table.php
// RÔLE    : Crée la table "produits" → cœur du système de stock
// COLONNES IMPORTANTES :
//   - reference     : code unique du produit (ex: GOR-0041)
//   - quantite      : stock actuel disponible
//   - seuil_alerte  : déclenche une alerte si quantite < seuil_alerte
//   - prix_achat    : prix auquel on a acheté le produit
//   - prix_vente    : prix auquel on vend le produit
//   - prix_revient  : prix de revient calculé (frais inclus)
//   - statut        : actif / inactif / archivé

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();                              // Ex: GOR-0041, CA-0128
            $table->string('nom');                                              // Nom complet du produit
            $table->text('description')->nullable();                            // Description détaillée
            $table->foreignId('categorie_id')                                  // Lien vers la table categories
                  ->constrained('categories')
                  ->onDelete('restrict');                                       // Interdit de supprimer une catégorie utilisée
            $table->decimal('prix_achat', 12, 2)->default(0);                  // Prix d'achat en FCFA
            $table->decimal('prix_vente', 12, 2)->default(0);                  // Prix de vente en FCFA
            $table->decimal('prix_revient', 12, 2)->default(0);                // Prix de revient (achat + frais)
            $table->integer('quantite')->default(0);                            // Stock disponible
            $table->integer('seuil_alerte')->default(5);                       // Alerte si stock < cette valeur
            $table->string('unite')->default('pièce');                         // Ex: pièce, gramme, lot
            $table->string('photo')->nullable();                                // Chemin vers l'image du produit
            $table->enum('statut', ['actif', 'inactif', 'archive'])
                  ->default('actif');
            $table->text('notes')->nullable();                                  // Notes internes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};