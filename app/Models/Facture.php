<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $table = 'factures';

    protected $fillable = [
        'numero',
        'type',           // 'facture' | 'devis' | 'avoir'
        'vente_id',
        'client_id',
        'montant_total',
        'statut',         // 'brouillon' | 'emise' | 'payee' | 'annulee'
        'date_emission',
        'date_echeance',
        'fichier_pdf',
        'notes',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'montant_total' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    // La facture est liée à une vente
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    // La facture appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeFactures($query)
    {
        return $query->where('type', 'facture');
    }

    public function scopeDevis($query)
    {
        return $query->where('type', 'devis');
    }

    public function scopePayees($query)
    {
        return $query->where('statut', 'payee');
    }

    public function scopeImpayees($query)
    {
        return $query->where('statut', 'emise');
    }

    // ── Génération du numéro ──────────────────────────────────────
    public static function genererNumero(string $type = 'facture'): string
    {
        $prefix  = $type === 'devis' ? 'DEV' : 'FAC';
        $derniere = static::where('type', $type)
            ->whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $numero = $derniere
            ? (int) substr($derniere->numero, -3) + 1
            : 1;

        return $prefix . '-' . date('Y') . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}
