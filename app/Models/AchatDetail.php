<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchatDetail extends Model
{
    // ⚠️ La table s'appelle achat_details (pas achat_detail)
    protected $table = 'achat_details';

    protected $fillable = [
        'achat_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'total_ligne',
    ];

    protected $casts = [
        'quantite'      => 'integer',
        'prix_unitaire' => 'decimal:2',
        'total_ligne'   => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function achat()
    {
        return $this->belongsTo(Achat::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
