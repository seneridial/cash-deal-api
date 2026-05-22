<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /api/users
    public function index(): JsonResponse
    {
        $users = User::orderBy('name')->get(['id','name','email','role','is_active','last_login_at','created_at']);
        return response()->json($users);
    }

    // POST /api/users
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,gerant,vendeur',
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = true;

        $user = User::create($data);

        return response()->json($user, 201);
    }

    // PUT /api/users/{id}
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'role'      => 'sometimes|in:admin,gerant,vendeur',
            'is_active' => 'sometimes|boolean',
            'password'  => 'nullable|string|min:8',
        ]);

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    // DELETE /api/users/{id}
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    // PUT /api/profil/password
public function changePassword(Request $request): JsonResponse
{
    $request->validate([
        'current_password' => 'required|string',
        'new_password'     => 'required|string|min:8|confirmed',
    ]);

    $user = $request->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'message' => 'Mot de passe actuel incorrect.'
        ], 422);
    }

    $user->update([
        'password' => Hash::make($request->new_password)
    ]);

    return response()->json([
        'message' => 'Mot de passe modifié avec succès.'
    ]);
}
}