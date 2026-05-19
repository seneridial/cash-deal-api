<?php
// FICHIER : database/migrations/2024_01_01_000007_create_factures_table.php
// RÔLE    : Crée la table "factures" liée aux ventes
// NOTE    : Une facture est générée depuis une vente. Elle peut être :
//           - "facture"   : document officiel de vente
//           - "devis"     : estimation avant achat (pas encore validée)
//           - "avoir"     : remboursement partiel ou total

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();                           // FAC-2026-041 ou DEV-2026-012
            $table->enum('type', ['facture', 'devis', 'avoir'])
                  ->default('facture');
            $table->foreignId('vente_id')
                  ->nullable()
                  ->constrained('ventes')
                  ->onDelete('set null');
            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->onDelete('set null');
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->enum('statut', ['brouillon', 'emise', 'payee', 'annulee'])
                  ->default('brouillon');
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();                    // Date limite de paiement
            $table->string('fichier_pdf')->nullable();                    // Chemin vers le PDF généré
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};