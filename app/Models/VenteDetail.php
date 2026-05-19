<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenteDetail extends Model
{
    // ⚠️ La table s'appelle vente_details (pas vente_detail)
    protected $table = 'vente_details';

    protected $fillable = [
        'vente_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'prix_achat_snapshot',  // Prix d'achat au moment de la vente (pour calcul bénéfice)
        'remise_ligne',
        'total_ligne',
    ];

    protected $casts = [
        'quantite'            => 'integer',
        'prix_unitaire'       => 'decimal:2',
        'prix_achat_snapshot' => 'decimal:2',
        'remise_ligne'        => 'decimal:2',
        'total_ligne'         => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // ── Accesseurs ────────────────────────────────────────────────

    // Bénéfice réalisé sur cette ligne
    public function getBeneficeLigneAttribute(): float
    {
        return ((float) $this->prix_unitaire - (float) $this->prix_achat_snapshot)
            * $this->quantite;
    }
}
