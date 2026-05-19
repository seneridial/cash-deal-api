<?php
// FICHIER : database/migrations/2024_01_01_000005_create_ventes_tables.php
// RÔLE    : Crée 2 tables liées :
//   - "ventes"        : en-tête de chaque vente (1 vente = 1 ligne)
//   - "vente_details" : lignes de produits de chaque vente (1 vente = N lignes)
//
// EXEMPLE :
//   ventes : id=1, client="Mamadou", total=270000, date=2026-05-07
//   vente_details : id=1, vente_id=1, produit_id=5, quantite=2, prix_unitaire=120000
//   vente_details : id=2, vente_id=1, produit_id=8, quantite=1, prix_unitaire=30000

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table principale des ventes ──────────────────────────────
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();                          // Ex: VNT-2026-041 (généré auto)
            $table->foreignId('client_id')
                  ->nullable()                                           // Vente anonyme possible
                  ->constrained('clients')
                  ->onDelete('set null');
            $table->foreignId('user_id')                                 // Vendeur qui a fait la vente
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->decimal('montant_ht', 12, 2)->default(0);           // Montant hors taxes
            $table->decimal('remise', 12, 2)->default(0);               // Remise en FCFA
            $table->decimal('montant_total', 12, 2)->default(0);        // Total final payé
            $table->decimal('montant_paye', 12, 2)->default(0);         // Montant déjà encaissé
            $table->decimal('montant_reste', 12, 2)->default(0);        // Reste à payer
            $table->enum('mode_paiement', ['especes', 'virement', 'cheque', 'mobile_money', 'credit'])
                  ->default('especes');
            $table->enum('statut', ['en_attente', 'paye', 'partiel', 'annule'])
                  ->default('en_attente');
            $table->date('date_vente');                                  // Date de la vente
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Lignes de détail de chaque vente ────────────────────────
        Schema::create('vente_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')
                  ->constrained('ventes')
                  ->onDelete('cascade');                                  // Supprime les lignes si la vente est supprimée
            $table->foreignId('produit_id')
                  ->constrained('produits')
                  ->onDelete('restrict');
            $table->integer('quantite');                                  // Quantité vendue
            $table->decimal('prix_unitaire', 12, 2);                     // Prix au moment de la vente
            $table->decimal('prix_achat_snapshot', 12, 2);               // Prix d'achat au moment de la vente (pour calcul bénéfice)
            $table->decimal('remise_ligne', 12, 2)->default(0);          // Remise sur cette ligne
            $table->decimal('total_ligne', 12, 2);                       // quantite × prix_unitaire - remise_ligne
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_details');
        Schema::dropIfExists('ventes');
    }
};