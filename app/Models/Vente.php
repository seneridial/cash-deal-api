<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $table = 'ventes';

    protected $fillable = [
        'numero',
        'client_id',
        'user_id',
        'montant_ht',
        'remise',
        'montant_total',
        'montant_paye',
        'montant_reste',
        'mode_paiement',
        'statut',
        'date_vente',
        'notes',
    ];

    protected $casts = [
        'date_vente'    => 'date',
        'montant_ht'    => 'decimal:2',
        'remise'        => 'decimal:2',
        'montant_total' => 'decimal:2',
        'montant_paye'  => 'decimal:2',
        'montant_reste' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    // La vente appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // La vente a été faite par un utilisateur (vendeur)
    public function vendeur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Une vente contient plusieurs lignes de produits
    public function details()
    {
        return $this->hasMany(VenteDetail::class);
    }

    // Une vente génère une facture
    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    // ── Accesseurs ────────────────────────────────────────────────

    // Bénéfice total de cette vente
    public function getBeneficeTotalAttribute(): float
    {
        return $this->details->sum(function ($detail) {
            return ($detail->prix_unitaire - $detail->prix_achat_snapshot) * $detail->quantite;
        });
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopePayees($query)
    {
        return $query->where('statut', 'paye');
    }

    public function scopeDuMois($query, $mois = null, $annee = null)
    {
        $mois  = $mois  ?? date('m');
        $annee = $annee ?? date('Y');
        return $query->whereMonth('date_vente', $mois)
                     ->whereYear('date_vente', $annee);
    }

    // ── Génération du numéro de vente ─────────────────────────────

    // Utilisation : Vente::genererNumero()
    public static function genererNumero(): string
    {
        $derniere = static::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $numero = $derniere
            ? (int) substr($derniere->numero, -3) + 1
            : 1;

        return 'VNT-' . date('Y') . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}
