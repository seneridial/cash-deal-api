<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ─────────────────────────────────────────────────
        DB::table('users')->insert([
            [
                'name'       => 'Administrateur',
                'email'      => 'admin@cashdeal.com',
                'password'   => Hash::make('Admin@1234'),
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Gérant Principal',
                'email'      => 'gerant@cashdeal.com',
                'password'   => Hash::make('Gerant@1234'),
                'role'       => 'gerant',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Vendeur Test',
                'email'      => 'vendeur@cashdeal.com',
                'password'   => Hash::make('Vendeur@1234'),
                'role'       => 'vendeur',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ── CATEGORIES ────────────────────────────────────────────
        DB::table('categories')->insert([
            [
                'nom'        => "Gard'or",
                'description'=> 'Bijoux et métaux précieux',
                'couleur'    => '#EF9F27',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom'        => 'Cash Auto',
                'description'=> 'Téléphones et électronique',
                'couleur'    => '#185FA5',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom'        => 'Accessoires',
                'description'=> 'Accessoires divers',
                'couleur'    => '#1D9E75',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ── PRODUITS ──────────────────────────────────────────────
        DB::table('produits')->insert([
            [
                'reference'   => 'GOR-0041',
                'nom'         => "Bague OR 18K — 5g",
                'description' => null,
                'categorie_id'=> 1,
                'prix_achat'  => 85000,
                'prix_vente'  => 120000,
                'prix_revient'=> 88000,
                'quantite'    => 18,
                'seuil_alerte'=> 3,
                'unite'       => 'pièce',
                'photo'       => null,
                'statut'      => 'actif',
                'notes'       => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'reference'   => 'GOR-0082',
                'nom'         => "Collier OR 14K — 8g",
                'description' => null,
                'categorie_id'=> 1,
                'prix_achat'  => 140000,
                'prix_vente'  => 210000,
                'prix_revient'=> 145000,
                'quantite'    => 7,
                'seuil_alerte'=> 3,
                'unite'       => 'pièce',
                'photo'       => null,
                'statut'      => 'actif',
                'notes'       => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'reference'   => 'GOR-0019',
                'nom'         => "Bracelet argent 925",
                'description' => null,
                'categorie_id'=> 1,
                'prix_achat'  => 22000,
                'prix_vente'  => 38000,
                'prix_revient'=> 23000,
                'quantite'    => 24,
                'seuil_alerte'=> 5,
                'unite'       => 'pièce',
                'photo'       => null,
                'statut'      => 'actif',
                'notes'       => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'reference'   => 'CA-0128',
                'nom'         => "iPhone 14 Pro 256Go",
                'description' => null,
                'categorie_id'=> 2,
                'prix_achat'  => 480000,
                'prix_vente'  => 650000,
                'prix_revient'=> 490000,
                'quantite'    => 3,
                'seuil_alerte'=> 5,
                'unite'       => 'pièce',
                'photo'       => null,
                'statut'      => 'actif',
                'notes'       => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'reference'   => 'CA-0055',
                'nom'         => "Samsung Galaxy S24",
                'description' => null,
                'categorie_id'=> 2,
                'prix_achat'  => 310000,
                'prix_vente'  => 420000,
                'prix_revient'=> 318000,
                'quantite'    => 0,
                'seuil_alerte'=> 5,
                'unite'       => 'pièce',
                'photo'       => null,
                'statut'      => 'actif',
                'notes'       => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // ── CLIENTS ───────────────────────────────────────────────
        // ⚠️ Insérer séparément clients et fournisseurs
        // car les fournisseurs ont le champ "entreprise"

        // Clients particuliers
        DB::table('clients')->insert([
            [
                'nom'        => 'Mamadou Diallo',
                'telephone'  => '+221 77 123 45 67',
                'telephone2' => null,
                'email'      => 'm.diallo@email.com',
                'adresse'    => null,
                'ville'      => 'Dakar',
                'type'       => 'client',
                'entreprise' => null,
                'notes'      => null,
                'is_vip'     => false,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom'        => 'Fatou Sow',
                'telephone'  => '+221 78 456 78 90',
                'telephone2' => null,
                'email'      => 'fatou.sow@gmail.com',
                'adresse'    => null,
                'ville'      => 'Dakar',
                'type'       => 'client',
                'entreprise' => null,
                'notes'      => null,
                'is_vip'     => false,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom'        => 'Rokhaya Mbaye',
                'telephone'  => '+221 70 987 65 43',
                'telephone2' => null,
                'email'      => 'r.mbaye@gmail.com',
                'adresse'    => null,
                'ville'      => 'Dakar',
                'type'       => 'client',
                'entreprise' => null,
                'notes'      => null,
                'is_vip'     => true,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Fournisseurs (même structure, entreprise renseignée)
            [
                'nom'        => "Gard'or Sénégal",
                'telephone'  => '+221 33 821 00 00',
                'telephone2' => null,
                'email'      => 'contact@gardor.sn',
                'adresse'    => null,
                'ville'      => 'Dakar',
                'type'       => 'fournisseur',
                'entreprise' => "Gard'or",
                'notes'      => null,
                'is_vip'     => false,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom'        => 'Cash Auto Dakar',
                'telephone'  => '+221 33 845 00 11',
                'telephone2' => null,
                'email'      => 'info@cashauto.sn',
                'adresse'    => null,
                'ville'      => 'Dakar',
                'type'       => 'fournisseur',
                'entreprise' => 'Cash Auto',
                'notes'      => null,
                'is_vip'     => false,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Base de données remplie avec succès !');
        $this->command->info('👤 Admin   : admin@cashdeal.com / Admin@1234');
        $this->command->info('👤 Gérant  : gerant@cashdeal.com / Gerant@1234');
        $this->command->info('👤 Vendeur : vendeur@cashdeal.com / Vendeur@1234');
    }
}