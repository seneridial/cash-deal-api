<?php

namespace App\Http\Controllers;

use App\Models\Mouvement;
use App\Models\Produit;
use App\Models\VenteDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MouvementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Mouvement::with(['produit.categorie', 'user']);

        if ($request->has('type'))       { $query->where('type', $request->type); }
        if ($request->has('produit_id')) { $query->where('produit_id', $request->produit_id); }
        if ($request->has('date_debut')) { $query->whereDate('date_mouvement', '>=', $request->date_debut); }
        if ($request->has('date_fin'))   { $query->whereDate('date_mouvement', '<=', $request->date_fin); }

        return response()->json($query->orderBy('date_mouvement', 'desc')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'produit_id'     => 'required|exists:produits,id',
            'type'           => 'required|in:entree,sortie',
            'quantite'       => 'required|integer|min:1',
            'prix_unitaire'  => 'nullable|numeric|min:0',
            'origine'        => 'nullable|string|max:255',
            'motif'          => 'nullable|string|max:255',
            'date_mouvement' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $produit = Produit::findOrFail($data['produit_id']);

            if ($data['type'] === 'sortie' && $produit->quantite < $data['quantite']) {
                abort(422, "Stock insuffisant. Disponible : {$produit->quantite}");
            }

            $data['user_id'] = $request->user()->id;
            $data['total']   = ($data['prix_unitaire'] ?? 0) * $data['quantite'];

            $mouvement = Mouvement::create($data);

            if ($data['type'] === 'entree') {
                $produit->increment('quantite', $data['quantite']);
            } else {
                $produit->decrement('quantite', $data['quantite']);
            }

            return response()->json($mouvement->load(['produit', 'user']), 201);
        });
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_entrees'        => Mouvement::where('type', 'entree')->sum('quantite'),
            'total_sorties'        => Mouvement::where('type', 'sortie')->sum('quantite'),
            'total_valeur_entrees' => Mouvement::where('type', 'entree')->sum('total'),
            'total_valeur_sorties' => Mouvement::where('type', 'sortie')->sum('total'),
        ]);
    }

    // GET /api/mouvements/etats — Vendus, Rachetés, Restants par produit
    public function etats(): JsonResponse
    {
        $produits = Produit::with('categorie')->actifs()->get();

        $etats = $produits->map(function ($produit) {
            $vendus   = VenteDetail::where('produit_id', $produit->id)->sum('quantite');
            $rachetes = Mouvement::where('produit_id', $produit->id)
                          ->where('type', 'entree')->sum('quantite');
            $restants = $produit->quantite;

            $ca_ventes   = VenteDetail::where('produit_id', $produit->id)
                             ->sum('total_ligne');
            $cout_achats = VenteDetail::where('produit_id', $produit->id)
                             ->selectRaw('SUM(prix_achat_snapshot * quantite) as total')
                             ->value('total') ?? 0;

            $benefice = $ca_ventes - $cout_achats;

            return [
                'produit'    => $produit->nom,
                'reference'  => $produit->reference,
                'categorie'  => $produit->categorie->nom ?? '',
                'vendus'     => $vendus,
                'rachetes'   => $rachetes,
                'restants'   => $restants,
                'ca_ventes'  => $ca_ventes,
                'cout_achats'=> $cout_achats,
                'benefice'   => $benefice,
                'perte'      => $benefice < 0 ? abs($benefice) : 0,
            ];
        });

        return response()->json($etats);
    }

    // GET /api/mouvements/pdf
    public function exportPdf(): \Illuminate\Http\Response
    {
        $mouvements = Mouvement::with(['produit.categorie', 'user'])
            ->orderBy('date_mouvement', 'desc')
            ->get();

        $pdf = Pdf::loadView('mouvements.pdf', compact('mouvements'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('depots-' . date('Y-m-d') . '.pdf');
    }
}