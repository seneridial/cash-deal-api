<?php
// ══════════════════════════════════════════════════════════════════
// FICHIER : app/Http/Controllers/AuthController.php
// RÔLE    : Gère la connexion, déconnexion, profil
// ══════════════════════════════════════════════════════════════════
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiant ou mot de passe incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $user->update(['last_login_at' => now()]);
        $user->tokens()->where('name', 'cash-deal-token')->delete();
        $token = $user->createToken('cash-deal-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->role,
                'last_login_at' => $user->last_login_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}