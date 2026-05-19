<?php
// FICHIER : database/migrations/2024_01_01_000006_create_achats_tables.php
// RÔLE    : Crée les tables pour gérer les achats auprès des fournisseurs
//   - "achats"        : en-tête (fournisseur, date, total...)
//   - "achat_details" : lignes de produits achetés

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achats', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();                           // Ex: ACH-2026-018
            $table->foreignId('fournisseur_id')
                  ->nullable()
                  ->constrained('clients')                               // Fournisseur = client de type fournisseur
                  ->onDelete('set null');
            $table->foreignId('user_id')                                 // Utilisateur qui a enregistré
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('montant_reste', 12, 2)->default(0);
            $table->enum('mode_paiement', ['especes', 'virement', 'cheque', 'mobile_money', 'credit'])
                  ->default('especes');
            $table->enum('statut', ['en_attente', 'recu', 'partiel', 'annule'])
                  ->default('en_attente');
            $table->date('date_achat');
            $table->date('date_livraison_prevue')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('achat_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achat_id')
                  ->constrained('achats')
                  ->onDelete('cascade');
            $table->foreignId('produit_id')
                  ->constrained('produits')
                  ->onDelete('restrict');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('total_ligne', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achat_details');
        Schema::dropIfExists('achats');
    }
};