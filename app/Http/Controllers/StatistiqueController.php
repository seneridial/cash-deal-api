<?php
// ══════════════════════════════════════════════════════════════════
// FICHIER : app/Http/Controllers/StatistiqueController.php
// RÔLE    : Retourne les données pour les graphiques et rapports
// ══════════════════════════════════════════════════════════════════
namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Achat;
use App\Models\Produit;
use App\Models\VenteDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatistiqueController extends Controller
{
    // GET /api/statistiques/dashboard
    // Retourne tout ce qu'il faut pour le tableau de bord
    public function dashboard(): JsonResponse
    {
        $mois  = date('m');
        $annee = date('Y');

        return response()->json([
            // Métriques du mois en cours
            'ventes_mois'     => Vente::whereMonth('date_vente', $mois)->whereYear('date_vente', $annee)->where('statut', '!=', 'annule')->sum('montant_total'),
            'achats_mois'     => Achat::whereMonth('date_achat', $mois)->whereYear('date_achat', $annee)->sum('montant_total'),
            'benefice_mois'   => VenteDetail::whereHas('vente', fn($q) => $q->whereMonth('date_vente', $mois)->whereYear('date_vente', $annee))->selectRaw('SUM((prix_unitaire - prix_achat_snapshot) * quantite) as b')->value('b') ?? 0,
            'nb_produits'     => Produit::where('statut', 'actif')->count(),

            // Alertes stock
            'alertes_stock'   => Produit::where('statut', 'actif')->where(fn($q) => $q->where('quantite', 0)->orWhereColumn('quantite', '<=', 'seuil_alerte'))->with('categorie')->limit(5)->get(),

            // Dernières ventes
            'dernieres_ventes' => Vente::with(['client'])->latest()->limit(5)->get(),

            // Derniers achats
            'derniers_achats'  => Achat::with(['fournisseur'])->latest()->limit(5)->get(),
        ]);
    }

    // GET /api/statistiques/evolution?annee=2026
    // Ventes et achats par mois (pour le graphique en barres)
    public function evolution(Request $request): JsonResponse
    {
        $annee = $request->get('annee', date('Y'));
        $data  = [];

        for ($m = 1; $m <= 12; $m++) {
            $data[] = [
                'mois'   => $m,
                'label'  => date('M', mktime(0, 0, 0, $m, 1)),
                'ventes' => Vente::whereMonth('date_vente', $m)->whereYear('date_vente', $annee)->sum('montant_total'),
                'achats' => Achat::whereMonth('date_achat', $m)->whereYear('date_achat', $annee)->sum('montant_total'),
            ];
        }

        return response()->json($data);
    }

    // GET /api/statistiques/top-produits?limit=10
    public function topProduits(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);

        $top = VenteDetail::selectRaw('produit_id, SUM(quantite) as total_qte, SUM(total_ligne) as total_ca, SUM((prix_unitaire - prix_achat_snapshot) * quantite) as total_benefice')
            ->groupBy('produit_id')
            ->orderByDesc('total_ca')
            ->limit($limit)
            ->with('produit.categorie')
            ->get();

        return response()->json($top);
    }
}