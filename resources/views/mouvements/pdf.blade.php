<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { background: #185FA5; color: #fff; padding: 16px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; }
        .header p  { font-size: 11px; opacity: 0.8; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #185FA5; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .badge-green { color: #27500A; background: #EAF3DE; padding: 2px 6px; border-radius: 10px; }
        .badge-red   { color: #791F1F; background: #FCEBEB; padding: 2px 6px; border-radius: 10px; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CASH DEAL — Rapport des Dépôts & Mouvements</h1>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Produit</th>
                <th>Catégorie</th>
                <th>Origine</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
                <th>Motif</th>
                <th>Date</th>
                <th>Par</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mouvements as $m)
            <tr>
                <td>
                    <span class="{{ $m->type === 'entree' ? 'badge-green' : 'badge-red' }}">
                        {{ $m->type === 'entree' ? 'Entrée' : 'Sortie' }}
                    </span>
                </td>
                <td>{{ $m->produit->nom ?? 'N/A' }}</td>
                <td>{{ $m->produit->categorie->nom ?? '' }}</td>
                <td>{{ $m->origine ?? '—' }}</td>
                <td>{{ $m->quantite }}</td>
                <td>{{ number_format($m->prix_unitaire, 0, ',', ' ') }} F</td>
                <td>{{ number_format($m->total, 0, ',', ' ') }} F</td>
                <td>{{ $m->motif ?? '—' }}</td>
                <td>{{ $m->date_mouvement->format('d/m/Y') }}</td>
                <td>{{ $m->user->name ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Cash Deal — Total : {{ count($mouvements) }} mouvements
    </div>
</body>
</html>