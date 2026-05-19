<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class FactureController extends Controller
{
    // GET /api/factures
    public function index(): JsonResponse
    {
        $factures = Facture::with(['client', 'vente'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($factures);
    }

    // GET /api/factures/{id}
    public function show(Facture $facture): JsonResponse
    {
        return response()->json(
            $facture->load(['client', 'vente.details.produit'])
        );
    }

    // GET /api/factures/{id}/pdf
    public function pdf(Facture $facture)
    {
        $facture->load(['client', 'vente.details.produit']);

        $pdf = Pdf::loadView('factures.pdf', compact('facture'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("facture-{$facture->numero}.pdf");
    }
}