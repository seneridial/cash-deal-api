<?php
// ══════════════════════════════════════════════════════════════════
// FICHIER : app/Http/Controllers/ClientController.php
// ══════════════════════════════════════════════════════════════════
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('telephone', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            });
        }

        if ($request->has('type')) { $query->where('type', $request->type); }

        return response()->json($query->orderBy('nom')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:255',
            'telephone'  => 'nullable|string|max:20',
            'telephone2' => 'nullable|string|max:20',
            'email'      => 'nullable|email',
            'adresse'    => 'nullable|string',
            'ville'      => 'nullable|string',
            'type'       => 'required|in:client,fournisseur,les_deux',
            'entreprise' => 'nullable|string',
            'notes'      => 'nullable|string',
            'is_vip'     => 'nullable|boolean',
        ]);

        $client = Client::create($data);
        return response()->json($client, 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json($client->load(['ventes' => fn($q) => $q->latest()->limit(10)]));
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $client->update($request->validate([
            'nom'       => 'sometimes|string|max:255',
            'telephone' => 'nullable|string',
            'email'     => 'nullable|email',
            'ville'     => 'nullable|string',
            'type'      => 'sometimes|in:client,fournisseur,les_deux',
            'is_vip'    => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]));
        return response()->json($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        if ($client->ventes()->exists()) {
            return response()->json(['message' => 'Client avec historique, désactivation uniquement.'], 422);
        }
        $client->delete();
        return response()->json(['message' => 'Client supprimé.']);
    }
}