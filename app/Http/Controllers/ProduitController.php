<?php
// FICHIER : app/Http/Controllers/ProduitController.php
// RÔLE    : CRUD complet des produits + gestion du stock
// ROUTES  : GET /produits, POST /produits, PUT /produits/{id}, DELETE /produits/{id}

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProduitController extends Controller
{
    // GET /api/produits?search=bague&categorie_id=1&statut=actif
    public function index(Request $request): JsonResponse
{
    $query = Produit::with('categorie');

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('nom', 'ilike', "%$search%")
              ->orWhere('reference', 'ilike', "%$search%");
        });
    }

    if ($request->has('categorie_id') && $request->categorie_id) {
        $query->where('categorie_id', $request->categorie_id);
    }

    if ($request->has('statut_stock')) {
        if ($request->statut_stock === 'rupture') {
            $query->where('quantite', 0);
        } elseif ($request->statut_stock === 'alerte') {
            $query->whereRaw('quantite > 0 AND quantite <= seuil_alerte');
        } elseif ($request->statut_stock === 'ok') {
            $query->whereRaw('quantite > seuil_alerte');
        }
    }

    $produits = $query->orderBy('nom')->paginate(20);

    return response()->json($produits);
}

    // GET /api/produits/{id}
    public function show(Produit $produit): JsonResponse
    {
        return response()->json($produit->load('categorie'));
    }
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference'    => 'required|string|unique:produits',
            'nom'          => 'required|string|max:255',
            'description'  => 'nullable|string',
            'origine'      => 'nullable|string|max:255',
            'categorie_id' => 'required|exists:categories,id',
            'prix_achat'   => 'required|numeric|min:0',
            'prix_vente'   => 'required|numeric|min:0',
            'prix_revient' => 'nullable|numeric|min:0',
            'quantite'     => 'required|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
            'unite'        => 'nullable|string',
            'statut'       => 'nullable|in:actif,inactif,archive',
            'notes'        => 'nullable|string',
            'photo'        => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $result = cloudinary()->upload($request->file('photo')->getRealPath(), [
                'folder' => 'cash-deal/produits',
            ]);
            $data['photo_url'] = $result->getSecurePath();
        }

        $data['prix_revient'] = $data['prix_revient'] ?? $data['prix_achat'];
        unset($data['photo']);

        $produit = Produit::create($data);
        return response()->json($produit->load('categorie'), 201);
    }

    public function update(Request $request, Produit $produit): JsonResponse
    {
        $data = $request->validate([
            'reference'    => 'sometimes|string|unique:produits,reference,' . $produit->id,
            'nom'          => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'origine'      => 'nullable|string|max:255',
            'categorie_id' => 'sometimes|exists:categories,id',
            'prix_achat'   => 'sometimes|numeric|min:0',
            'prix_vente'   => 'sometimes|numeric|min:0',
            'prix_revient' => 'nullable|numeric|min:0',
            'quantite'     => 'sometimes|integer|min:0',
            'seuil_alerte' => 'nullable|integer|min:0',
            'statut'       => 'nullable|in:actif,inactif,archive',
            'notes'        => 'nullable|string',
            'photo'        => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $result = cloudinary()->upload($request->file('photo')->getRealPath(), [
                'folder' => 'cash-deal/produits',
            ]);
            $data['photo_url'] = $result->getSecurePath();
        }

        unset($data['photo']);
        $produit->update($data);
        return response()->json($produit->load('categorie'));
    }

    // DELETE /api/produits/{id}
   public function destroy(Produit $produit): JsonResponse
{
    $produit->delete();
    return response()->json(['message' => 'Produit supprimé.']);
}

    // GET /api/produits/stats
    // Retourne les statistiques du stock pour le dashboard
    public function stats(): JsonResponse
{
    return response()->json([
        'total'        => Produit::where('statut', 'actif')->count(),
        'en_stock'     => Produit::where('statut', 'actif')
                            ->where('quantite', '>', 0)
                            ->count(),
        'stock_faible' => Produit::where('statut', 'actif')
                            ->whereRaw('quantite > 0 AND quantite <= seuil_alerte')
                            ->count(),
        'rupture'      => Produit::where('statut', 'actif')
                            ->where('quantite', 0)
                            ->count(),
        'alertes'      => Produit::where('statut', 'actif')
                            ->whereRaw('quantite <= seuil_alerte')
                            ->with('categorie')
                            ->limit(5)
                            ->get(),
    ]);
}
}