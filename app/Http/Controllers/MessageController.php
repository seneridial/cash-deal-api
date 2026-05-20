<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    // GET /api/messages
    public function index(): JsonResponse
    {
        $messages = Message::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    // POST /api/messages
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'nom'       => 'required|string|max:255',
            'email'     => 'nullable|email',
            'telephone' => 'nullable|string',
            'sujet'     => 'required|string|max:255',
            'message'   => 'required|string',
        ]);

        $message = Message::create($data);

        return response()->json($message, 201);
    }

    // PUT /api/messages/{id}/lire
    public function lire(Message $message): JsonResponse
    {
        $message->update([
            'statut' => 'lu',
            'lu_at'  => now(),
        ]);

        return response()->json($message);
    }

    // PUT /api/messages/{id}/repondre
    public function repondre(Request $request, Message $message): JsonResponse
    {
        $request->validate([
            'reponse' => 'required|string',
        ]);

        $message->update([
            'statut'  => 'repondu',
            'reponse' => $request->reponse,
        ]);

        return response()->json($message);
    }

    // GET /api/messages/non-lus
    public function nonLus(): JsonResponse
    {
        return response()->json([
            'count' => Message::where('statut', 'non_lu')->count(),
        ]);
    }
}