<?php
// FICHIER : app/Http/Controllers/VenteController.php
// RÔLE    : Enregistre une vente et met à jour le stock automatiquement
// IMPORTANT: Lors d'une vente, la quantité des produits est décrémentée automatiquement

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VenteController extends Controller
{
    // GET /api/ventes
    public function index(Request $request): JsonResponse
    {
        $query = Vente::with(['client', 'vendeur', 'details.produit']);

        if ($request->has('client_id'))    { $query->where('client_id', $request->client_id); }
        if ($request->has('statut'))       { $query->where('statut', $request->statut); }
        if ($request->has('date_debut'))   { $query->whereDate('date_vente', '>=', $request->date_debut); }
        if ($request->has('date_fin'))     { $query->whereDate('date_vente', '<=', $request->date_fin); }

        $ventes = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($ventes);
    }

    // POST /api/ventes
    // Corps attendu :
    // {
    //   "client_id": 1,           (optionnel)
    //   "date_vente": "2026-05-07",
    //   "mode_paiement": "especes",
    //   "remise": 0,
    //   "notes": "...",
    //   "details": [
    //     { "produit_id": 1, "quantite": 2, "prix_unitaire": 120000 }
    //   ]
    // }
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'            => 'nullable|exists:clients,id',
            'date_vente'           => 'required|date',
            'mode_paiement'        => 'required|in:especes,virement,cheque,mobile_money,credit',
            'remise'               => 'nullable|numeric|min:0',
            'montant_paye'         => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string',
            'details'              => 'required|array|min:1',
            'details.*.produit_id' => 'required|exists:produits,id',
            'details.*.quantite'   => 'required|integer|min:1',
            'details.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        // Utiliser une transaction DB pour garantir la cohérence
        // Si une étape échoue, tout est annulé
        return DB::transaction(function () use ($data, $request) {

            $montantHt    = 0;
            $detailsToSave = [];

            // ── Calculer les totaux et vérifier le stock ──────────────
            foreach ($data['details'] as $ligne) {
                $produit = Produit::findOrFail($ligne['produit_id']);

                // Vérification du stock disponible
                if ($produit->quantite < $ligne['quantite']) {
                    abort(422, "Stock insuffisant pour : {$produit->nom}. Disponible : {$produit->quantite}");
                }

                $totalLigne = $ligne['quantite'] * $ligne['prix_unitaire'];
                $montantHt += $totalLigne;

                $detailsToSave[] = [
                    'produit'              => $produit,
                    'quantite'             => $ligne['quantite'],
                    'prix_unitaire'        => $ligne['prix_unitaire'],
                    'prix_achat_snapshot'  => $produit->prix_achat,  // Sauvegarde du prix d'achat actuel
                    'total_ligne'          => $totalLigne,
                ];
            }

            $remise       = $data['remise'] ?? 0;
            $montantTotal = $montantHt - $remise;
            $montantPaye  = $data['montant_paye'] ?? $montantTotal;
            $montantReste = max(0, $montantTotal - $montantPaye);

            // Déterminer le statut automatiquement
            $statut = 'en_attente';
            if ($montantPaye >= $montantTotal)             $statut = 'paye';
            elseif ($montantPaye > 0)                      $statut = 'partiel';

            // ── Créer la vente ────────────────────────────────────────
            $vente = Vente::create([
                'numero'         => 'VNT-' . date('Y') . '-' . str_pad(Vente::count() + 1, 3, '0', STR_PAD_LEFT),
                'client_id'      => $data['client_id'] ?? null,
                'user_id'        => $request->user()->id,
                'montant_ht'     => $montantHt,
                'remise'         => $remise,
                'montant_total'  => $montantTotal,
                'montant_paye'   => $montantPaye,
                'montant_reste'  => $montantReste,
                'mode_paiement'  => $data['mode_paiement'],
                'statut'         => $statut,
                'date_vente'     => $data['date_vente'],
                'notes'          => $data['notes'] ?? null,
            ]);

            // ── Créer les lignes et décrémenter le stock ──────────────
            foreach ($detailsToSave as $d) {
                VenteDetail::create([
                    'vente_id'             => $vente->id,
                    'produit_id'           => $d['produit']->id,
                    'quantite'             => $d['quantite'],
                    'prix_unitaire'        => $d['prix_unitaire'],
                    'prix_achat_snapshot'  => $d['prix_achat_snapshot'],
                    'total_ligne'          => $d['total_ligne'],
                ]);

                // DÉCRÉMENTER LE STOCK DU PRODUIT
                $d['produit']->decrement('quantite', $d['quantite']);
            }

            // ── Créer la facture automatiquement ─────────────────────
            Facture::create([
                'numero'         => 'FAC-' . date('Y') . '-' . str_pad(Facture::count() + 1, 3, '0', STR_PAD_LEFT),
                'type'           => 'facture',
                'vente_id'       => $vente->id,
                'client_id'      => $data['client_id'] ?? null,
                'montant_total'  => $montantTotal,
                'statut'         => $statut === 'paye' ? 'payee' : 'emise',
                'date_emission'  => $data['date_vente'],
            ]);

            return response()->json($vente->load(['client', 'details.produit', 'facture']), 201);
        });
    }

    // GET /api/ventes/stats — statistiques pour le dashboard
    public function stats(Request $request): JsonResponse
    {
        $mois  = $request->get('mois', date('m'));
        $annee = $request->get('annee', date('Y'));

        return response()->json([
            'total_ventes_mois'    => Vente::whereMonth('date_vente', $mois)->whereYear('date_vente', $annee)->where('statut', '!=', 'annule')->sum('montant_total'),
            'nb_ventes_mois'       => Vente::whereMonth('date_vente', $mois)->whereYear('date_vente', $annee)->count(),
            'total_benefice_mois'  => VenteDetail::whereHas('vente', fn($q) => $q->whereMonth('date_vente', $mois)->whereYear('date_vente', $annee))->selectRaw('SUM((prix_unitaire - prix_achat_snapshot) * quantite) as benefice')->value('benefice') ?? 0,
        ]);
    }
}