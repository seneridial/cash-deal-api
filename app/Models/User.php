<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // ── Colonnes autorisées à être remplies en masse ──────────────
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    // ── Colonnes jamais retournées dans les réponses JSON ─────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Conversions automatiques de types ─────────────────────────
    protected $casts = [
        'password'      => 'hashed',       // Hash auto à l'écriture
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────
    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function achats()
    {
        return $this->hasMany(Achat::class);
    }

    // ── Helpers rôles ─────────────────────────────────────────────
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isGerant(): bool  { return $this->role === 'gerant'; }
    public function isVendeur(): bool { return $this->role === 'vendeur'; }
}
