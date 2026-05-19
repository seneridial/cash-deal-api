<?php
// ══════════════════════════════════════════════════════════════════
// FICHIER : app/Http/Controllers/AchatController.php
// RÔLE    : Enregistre les achats et incrémente le stock
// ══════════════════════════════════════════════════════════════════
namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\AchatDetail;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AchatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Achat::with(['fournisseur', 'user', 'details.produit']);
        if ($request->has('statut')) { $query->where('statut', $request->statut); }
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    // POST /api/achats
    // Enregistre l'achat ET incrémente le stock de chaque produit
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fournisseur_id'         => 'nullable|exists:clients,id',
            'date_achat'             => 'required|date',
            'mode_paiement'          => 'required|in:especes,virement,cheque,mobile_money,credit',
            'montant_paye'           => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
            'details'                => 'required|array|min:1',
            'details.*.produit_id'   => 'required|exists:produits,id',
            'details.*.quantite'     => 'required|integer|min:1',
            'details.*.prix_unitaire'=> 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $montantTotal = 0;

            foreach ($data['details'] as $ligne) {
                $montantTotal += $ligne['quantite'] * $ligne['prix_unitaire'];
            }

            $montantPaye  = $data['montant_paye'] ?? $montantTotal;
            $montantReste = max(0, $montantTotal - $montantPaye);
            $statut       = $montantPaye >= $montantTotal ? 'recu' : ($montantPaye > 0 ? 'partiel' : 'en_attente');

            $achat = Achat::create([
                'numero'         => 'ACH-' . date('Y') . '-' . str_pad(Achat::count() + 1, 3, '0', STR_PAD_LEFT),
                'fournisseur_id' => $data['fournisseur_id'] ?? null,
                'user_id'        => $request->user()->id,
                'montant_total'  => $montantTotal,
                'montant_paye'   => $montantPaye,
                'montant_reste'  => $montantReste,
                'mode_paiement'  => $data['mode_paiement'],
                'statut'         => $statut,
                'date_achat'     => $data['date_achat'],
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($data['details'] as $ligne) {
                AchatDetail::create([
                    'achat_id'     => $achat->id,
                    'produit_id'   => $ligne['produit_id'],
                    'quantite'     => $ligne['quantite'],
                    'prix_unitaire'=> $ligne['prix_unitaire'],
                    'total_ligne'  => $ligne['quantite'] * $ligne['prix_unitaire'],
                ]);

                // INCRÉMENTER LE STOCK DU PRODUIT
                Produit::find($ligne['produit_id'])->increment('quantite', $ligne['quantite']);

                // Mettre à jour le prix d'achat si changement
                Produit::find($ligne['produit_id'])->update([
                    'prix_achat'   => $ligne['prix_unitaire'],
                    'prix_revient' => $ligne['prix_unitaire'],
                ]);
            }

            return response()->json($achat->load(['fournisseur', 'details.produit']), 201);
        });
    }
}