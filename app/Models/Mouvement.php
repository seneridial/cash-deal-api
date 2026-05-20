<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    protected $table = 'mouvements';

    protected $fillable = [
        'produit_id', 'user_id', 'type',
        'quantite', 'prix_unitaire', 'total',
        'origine', 'motif', 'date_mouvement', 'notes',
    ];

    protected $casts = [
        'date_mouvement' => 'date',
        'prix_unitaire'  => 'decimal:2',
        'total'          => 'decimal:2',
    ];

    public function produit() { return $this->belongsTo(Produit::class); }
    public function user()    { return $this->belongsTo(User::class); }
}