<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'client_id', 'nom', 'email', 'telephone',
        'sujet', 'message', 'statut', 'reponse', 'lu_at',
    ];

    protected $casts = [
        'lu_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}