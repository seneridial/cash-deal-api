<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'reference',
        'nom',
        'description',
        'categorie_id',
        'prix_achat',
        'prix_vente',
        'prix_revient',
        'quantite',
        'seuil_alerte',
        'unite',
        'photo',
        'statut',
        'notes',
    ];

    protected $casts = [
        'prix_achat'   => 'decimal:2',
        'prix_vente'   => 'decimal:2',
        'prix_revient' => 'decimal:2',
        'quantite'     => 'integer',
        'seuil_alerte' => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────

    // Un produit appartient à une catégorie
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    // Un produit peut apparaître dans plusieurs lignes de vente
    public function venteDetails()
    {
        return $this->hasMany(VenteDetail::class);
    }

    // Un produit peut apparaître dans plusieurs lignes d'achat
    public function achatDetails()
    {
        return $this->hasMany(AchatDetail::class);
    }

    // ── Accesseurs (valeurs calculées) ────────────────────────────

    // Bénéfice brut par unité vendue
    public function getBeneficeUnitaireAttribute(): float
    {
        return (float) $this->prix_vente - (float) $this->prix_revient;
    }

    // Valeur totale du stock
    public function getValeurStockAttribute(): float
    {
        return (float) $this->prix_revient * $this->quantite;
    }

    // ── Helpers état du stock ─────────────────────────────────────

    public function enRupture(): bool
    {
        return $this->quantite === 0;
    }

    public function stockBas(): bool
    {
        return $this->quantite > 0 && $this->quantite <= $this->seuil_alerte;
    }

    public function stockOk(): bool
    {
        return $this->quantite > $this->seuil_alerte;
    }

    // Retourne le statut stock sous forme de texte
    public function getStatutStockAttribute(): string
    {
        if ($this->enRupture())  return 'rupture';
        if ($this->stockBas())   return 'alerte';
        return 'ok';
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeEnRupture($query)
    {
        return $query->where('quantite', 0);
    }

    public function scopeStockBas($query)
    {
        return $query->whereRaw('"quantite" > 0 AND "quantite" <= "seuil_alerte"');
    }
}
