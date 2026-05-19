<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    protected $table = 'achats';

    protected $fillable = [
        'numero',
        'fournisseur_id',
        'user_id',
        'montant_total',
        'montant_paye',
        'montant_reste',
        'mode_paiement',
        'statut',
        'date_achat',
        'date_livraison_prevue',
        'notes',
    ];

    protected $casts = [
        'date_achat'            => 'date',
        'date_livraison_prevue' => 'date',
        'montant_total'         => 'decimal:2',
        'montant_paye'          => 'decimal:2',
        'montant_reste'         => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    // L'achat vient d'un fournisseur (qui est un Client avec type=fournisseur)
    public function fournisseur()
    {
        return $this->belongsTo(Client::class, 'fournisseur_id');
    }

    // L'achat a été enregistré par un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un achat contient plusieurs lignes de produits
    public function details()
    {
        return $this->hasMany(AchatDetail::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeDuMois($query, $mois = null, $annee = null)
    {
        $mois  = $mois  ?? date('m');
        $annee = $annee ?? date('Y');
        return $query->whereMonth('date_achat', $mois)
                     ->whereYear('date_achat', $annee);
    }

    public function scopeRecus($query)
    {
        return $query->where('statut', 'recu');
    }

    // ── Génération du numéro d'achat ──────────────────────────────
    public static function genererNumero(): string
    {
        $derniere = static::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $numero = $derniere
            ? (int) substr($derniere->numero, -3) + 1
            : 1;

        return 'ACH-' . date('Y') . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}
