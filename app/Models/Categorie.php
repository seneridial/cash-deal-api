<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    // ── Nom de la table (Laravel cherche "categories" par défaut) ─
    protected $table = 'categories';

    protected $fillable = [
        'nom',
        'description',
        'couleur',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────

    // Une catégorie possède plusieurs produits
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    // ── Scopes (filtres réutilisables) ────────────────────────────

    // Utilisation : Categorie::actives()->get()
    public function scopeActives($query)
    {
        return $query->where('is_active', true);
    }
}
