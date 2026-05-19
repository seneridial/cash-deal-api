<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'nom',
        'telephone',
        'telephone2',
        'email',
        'adresse',
        'ville',
        'type',          // 'client' | 'fournisseur' | 'les_deux'
        'entreprise',
        'notes',
        'is_vip',
        'is_active',
    ];

    protected $casts = [
        'is_vip'    => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────

    // Un client peut avoir plusieurs ventes
    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    // Un fournisseur peut avoir plusieurs achats
    public function achats()
    {
        return $this->hasMany(Achat::class, 'fournisseur_id');
    }

    // Un client peut avoir plusieurs factures
    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    // ── Accesseurs ────────────────────────────────────────────────

    // Total dépensé par ce client
    public function getTotalAchatsAttribute(): float
    {
        return (float) $this->ventes()
            ->whereIn('statut', ['paye', 'partiel'])
            ->sum('montant_total');
    }

    // Nombre de ventes
    public function getNbAchatsAttribute(): int
    {
        return $this->ventes()->count();
    }

    // Initiales pour l'avatar
    public function getInitialesAttribute(): string
    {
        $mots = explode(' ', $this->nom);
        $initiales = '';
        foreach (array_slice($mots, 0, 2) as $mot) {
            $initiales .= strtoupper($mot[0]);
        }
        return $initiales;
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeClients($query)
    {
        return $query->whereIn('type', ['client', 'les_deux']);
    }

    public function scopeFournisseurs($query)
    {
        return $query->whereIn('type', ['fournisseur', 'les_deux']);
    }

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }
}
