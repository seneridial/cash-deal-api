<?php
// FICHIER : database/migrations/2024_01_01_000004_create_clients_table.php
// RÔLE    : Crée la table "clients" qui stocke clients ET fournisseurs
// NOTE    : Le champ "type" distingue client / fournisseur / les deux

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');                                                // Nom complet ou raison sociale
            $table->string('telephone')->nullable();                              // Numéro principal
            $table->string('telephone2')->nullable();                             // Numéro secondaire
            $table->string('email')->nullable();                                  // Email de contact
            $table->string('adresse')->nullable();                                // Adresse physique
            $table->string('ville')->nullable();                                  // Ville
            $table->enum('type', ['client', 'fournisseur', 'les_deux'])
                  ->default('client');
            $table->string('entreprise')->nullable();                             // Nom de l'entreprise si pro
            $table->text('notes')->nullable();                                    // Notes internes
            $table->boolean('is_vip')->default(false);                            // Client VIP
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};