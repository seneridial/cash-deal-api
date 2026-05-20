<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MouvementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;


// ── Routes publiques ──────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── Routes protégées ──────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Statistiques
    Route::get('/statistiques/dashboard',    [StatistiqueController::class, 'dashboard']);
    Route::get('/statistiques/evolution',    [StatistiqueController::class, 'evolution']);
    Route::get('/statistiques/top-produits', [StatistiqueController::class, 'topProduits']);

    // Produits
    Route::get('/produits/stats',    [ProduitController::class, 'stats']);
    Route::get('/produits',          [ProduitController::class, 'index']);
    Route::get('/produits/{produit}',[ProduitController::class, 'show']);
    Route::middleware('role:admin,gerant')->group(function () {
        Route::post('/produits',            [ProduitController::class, 'store']);
        Route::put('/produits/{produit}',   [ProduitController::class, 'update']);
        Route::delete('/produits/{produit}',[ProduitController::class, 'destroy']);
    });

    // Clients
    Route::get('/clients',          [ClientController::class, 'index']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::middleware('role:admin,gerant')->group(function () {
        Route::post('/clients',           [ClientController::class, 'store']);
        Route::put('/clients/{client}',   [ClientController::class, 'update']);
        Route::delete('/clients/{client}',[ClientController::class, 'destroy']);
    });

    // Ventes
    Route::get('/ventes/stats', [VenteController::class, 'stats']);
    Route::get('/ventes',       [VenteController::class, 'index']);
    Route::post('/ventes',      [VenteController::class, 'store']);

    // Achats
    Route::middleware('role:admin,gerant')->group(function () {
        Route::get('/achats',  [AchatController::class, 'index']);
        Route::post('/achats', [AchatController::class, 'store']);
    });

    // Factures
    Route::get('/factures',               [FactureController::class, 'index']);
    Route::get('/factures/{facture}/pdf', [FactureController::class, 'pdf']);
    Route::get('/factures/{facture}',     [FactureController::class, 'show']);

    // Utilisateurs (admin uniquement)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users',           [UserController::class, 'index']);
        Route::post('/users',          [UserController::class, 'store']);
        Route::put('/users/{user}',    [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    // Mouvements / Dépôts
    Route::get('/mouvements/stats',  [MouvementController::class, 'stats']);
    Route::get('/mouvements/etats',  [MouvementController::class, 'etats']);
    Route::get('/mouvements/pdf',    [MouvementController::class, 'exportPdf']);
    Route::get('/mouvements',        [MouvementController::class, 'index']);
    Route::post('/mouvements',       [MouvementController::class, 'store']);

    // Messages / Contact
    Route::get('/messages/non-lus',          [MessageController::class, 'nonLus']);
    Route::get('/messages',                  [MessageController::class, 'index']);
    Route::post('/messages',                 [MessageController::class, 'store']);
    Route::put('/messages/{message}/lire',   [MessageController::class, 'lire']);
    Route::put('/messages/{message}/repondre', [MessageController::class, 'repondre']);

});