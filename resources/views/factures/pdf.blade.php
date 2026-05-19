<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #111827; }

        .header { background: #185FA5; color: #fff; padding: 20px 30px; display: flex; justify-content: space-between; }
        .header-left h1 { font-size: 22px; font-weight: 700; }
        .header-left p  { font-size: 12px; opacity: 0.8; margin-top: 4px; }
        .header-right   { text-align: right; }
        .header-right h2 { font-size: 18px; font-weight: 600; }
        .header-right p  { font-size: 12px; opacity: 0.8; }

        .body { padding: 30px; }

        .info-grid { display: flex; gap: 20px; margin-bottom: 24px; }
        .info-box  { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; }
        .info-box h4 { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .info-box p  { font-size: 13px; color: #111827; margin-bottom: 3px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #185FA5; color: #fff; padding: 10px 12px; text-align: left; font-size: 12px; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        tbody tr:last-child td { border: none; }
        tbody tr:nth-child(even) td { background: #f9fafb; }

        .totaux { margin-left: auto; width: 280px; }
        .totaux-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .totaux-row.total { font-size: 15px; font-weight: 700; color: #185FA5; border: none; padding-top: 10px; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 11px; color: #6b7280; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-green { background: #EAF3DE; color: #27500A; }
        .badge-amber { background: #FAEEDA; color: #633806; }
        .badge-blue  { background: #E6F1FB; color: #0C447C; }
    </style>
</head>
<body>

    <!-- En-tête -->
    <div class="header">
        <div class="header-left">
            <h1>CASH DEAL</h1>
            <p>Logiciel de gestion de stock</p>
        </div>
        <div class="header-right">
            <h2>{{ strtoupper($facture->type) }}</h2>
            <p>N° {{ $facture->numero }}</p>
            <p>Date : {{ $facture->date_emission->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="body">

        <!-- Infos client et facture -->
        <div class="info-grid">
            <div class="info-box">
                <h4>Émetteur</h4>
                <p><strong>Cash Deal</strong></p>
                <p>Dakar, Sénégal</p>
                <p>contact@cashdeal.sn</p>
            </div>
            <div class="info-box">
                <h4>Client</h4>
                @if($facture->client)
                    <p><strong>{{ $facture->client->nom }}</strong></p>
                    @if($facture->client->telephone)
                        <p>📞 {{ $facture->client->telephone }}</p>
                    @endif
                    @if($facture->client->email)
                        <p>✉️ {{ $facture->client->email }}</p>
                    @endif
                    @if($facture->client->ville)
                        <p>📍 {{ $facture->client->ville }}</p>
                    @endif
                @else
                    <p>Client anonyme</p>
                @endif
            </div>
            <div class="info-box">
                <h4>Détails</h4>
                <p>Numéro : <strong>{{ $facture->numero }}</strong></p>
                <p>Date : {{ $facture->date_emission->format('d/m/Y') }}</p>
                @if($facture->date_echeance)
                    <p>Échéance : {{ $facture->date_echeance->format('d/m/Y') }}</p>
                @endif
                <p>Statut :
                    <span class="badge {{ $facture->statut === 'payee' ? 'badge-green' : ($facture->statut === 'emise' ? 'badge-blue' : 'badge-amber') }}">
                        {{ ucfirst($facture->statut) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Lignes de produits -->
        @if($facture->vente && $facture->vente->details->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produit</th>
                    <th>Référence</th>
                    <th style="text-align:right;">Prix unitaire</th>
                    <th style="text-align:center;">Quantité</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->vente->details as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $detail->produit->nom ?? 'N/A' }}</td>
                    <td style="color:#6b7280; font-size:12px;">{{ $detail->produit->reference ?? '' }}</td>
                    <td style="text-align:right;">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} F</td>
                    <td style="text-align:center;">{{ $detail->quantite }}</td>
                    <td style="text-align:right; font-weight:500;">{{ number_format($detail->total_ligne, 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux -->
        <div class="totaux">
            <div class="totaux-row">
                <span>Sous-total HT</span>
                <span>{{ number_format($facture->vente->montant_ht, 0, ',', ' ') }} F</span>
            </div>
            @if($facture->vente->remise > 0)
            <div class="totaux-row">
                <span>Remise</span>
                <span>- {{ number_format($facture->vente->remise, 0, ',', ' ') }} F</span>
            </div>
            @endif
            <div class="totaux-row total">
                <span>TOTAL</span>
                <span>{{ number_format($facture->montant_total, 0, ',', ' ') }} F</span>
            </div>
            @if($facture->vente->montant_reste > 0)
            <div class="totaux-row" style="color:#dc2626;">
                <span>Reste à payer</span>
                <span>{{ number_format($facture->vente->montant_reste, 0, ',', ' ') }} F</span>
            </div>
            @endif
        </div>
        @else
        <div style="text-align:center; padding:2rem; color:#6b7280; background:#f9fafb; border-radius:8px;">
            Aucune ligne de détail disponible
        </div>
        @endif

        <!-- Notes -->
        @if($facture->notes)
        <div style="margin-top:24px; background:#f9fafb; border-radius:8px; padding:14px;">
            <h4 style="font-size:12px; color:#6b7280; margin-bottom:6px;">NOTES</h4>
            <p>{{ $facture->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Cash Deal — Logiciel de gestion de stock | Dakar, Sénégal</p>
            <p style="margin-top:4px;">Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>

    </div>
</body>
</html>