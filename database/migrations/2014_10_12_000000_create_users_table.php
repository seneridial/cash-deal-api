<?php
// FICHIER : database/migrations/2024_01_01_000001_create_users_table.php
// RÔLE    : Crée la table "users" qui stocke les comptes utilisateurs du système
// ACTION  : php artisan migrate  (exécute ce fichier)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                                        // Clé primaire auto-incrémentée
            $table->string('name');                                              // Nom complet de l'utilisateur
            $table->string('email')->unique();                                   // Email unique (sert de login)
            $table->string('password');                                          // Mot de passe hashé (bcrypt)
            $table->enum('role', ['admin', 'gerant', 'vendeur'])                // Rôle : détermine les accès
                  ->default('vendeur');
            $table->boolean('is_active')->default(true);                         // Compte actif/désactivé
            $table->timestamp('last_login_at')->nullable();                      // Date dernière connexion
            $table->rememberToken();                                             // Token "se souvenir de moi"
            $table->timestamps();                                                // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');  // Supprime la table si on annule la migration
    }
};