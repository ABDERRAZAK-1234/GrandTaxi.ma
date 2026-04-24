<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .ticket {
            width: 100%;
            max-width: 800px;
            display: flex;
            filter: drop-shadow(0 25px 50px rgba(0,0,0,0.5));
            border-radius: 24px;
            overflow: hidden;
        }

        /* --- PARTIE GAUCHE (MAIN) --- */
        .main {
            flex: 2;
            background: white;
            padding: 0;
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color: #000000; }
        .brand_ma {font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color:#22c55e }
        .status {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            color: #22c55e;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .route-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px 30px;
            background: #f8fafc;
        }

        .city h2 { font-size: 32px; font-weight: 800; color: #1e293b; margin: 0; line-height: 1; }
        .city p { font-size: 12px; color: #64748b; text-transform: uppercase; margin-top: 8px; font-weight: 600; }

        .path {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
            color: #cbd5e1;
        }
        .path-line { width: 100%; height: 2px; border-top: 2px dashed #cbd5e1; position: relative; }
        .path-line::after {
            content: "✈";
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #f8fafc;
            padding: 0 10px;
            font-size: 18px;
            color: #3b82f6;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 30px;
        }

        .info-block span { display: block; }
        .label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 5px; }
        .value { font-size: 15px; font-weight: 700; color: #1e293b; }

        .seats-strip {
            margin: 0 30px 30px;
            padding: 15px;
            background: #eff6ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .seat-pill {
            background: #3b82f6;
            color: white;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        /* --- PARTIE DROITE (STUB) --- */
        .stub {
            flex: 0.8;
            background: #1e293b;
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-left: 2px dashed rgba(255,255,255,0.1);
            position: relative;
        }

        /* Perforations */
        .stub::before, .stub::after {
            content: "";
            position: absolute;
            left: -12px;
            width: 24px;
            height: 24px;
            background: #0f172a;
            border-radius: 50%;
        }
        .stub::before { top: -12px; }
        .stub::after { bottom: -12px; }

        .price-section { text-align: center; margin-top: 20px; }
        .price-large { font-size: 42px; font-weight: 800; color: white; display: block; }
        .price-curr { font-size: 14px; opacity: 0.6; }

        .barcode-area {
            text-align: center;
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
        }
        .barcode-img {
            width: 100%;
            height: 50px;
            background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 4px);
        }

        .ref-code {
            font-size: 9px;
            color: rgba(255,255,255,0.4);
            text-align: center;
            margin-top: 15px;
            font-family: monospace;
        }

        @media print {
            body { background: white; padding: 0; }
            .ticket { filter: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<div class="ticket">
    <div class="main">
        <div class="header">
            <div class="brand">GrandTaxi<span class="brand_ma">.ma</span></div>
            <div class="status">Confirmation Instantanée</div>
        </div>

        <div class="route-container">
            <div class="city">
                <h2>{{ $reservation->trajet->villeDepart->nom }}</h2>
                <p>Point de Départ</p>
            </div>
            <div class="path">
                <div class="path-line"></div>
            </div>
            <div class="city" style="text-align: right;">
                <h2>{{ $reservation->trajet->villeArrivee->nom }}</h2>
                <p>Destination</p>
            </div>
        </div>

        <div class="details-grid">
            <div class="info-block">
                <span class="label">Passager</span>
                <span class="value">{{ $reservation->user->prenom }} {{ $reservation->user->nom }}</span>
            </div>
            <div class="info-block">
                <span class="label">Numéro Billet</span>
                <span class="value">#{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-block">
                <span class="label">Date du Voyage</span>
                <span class="value">{{ $reservation->created_at->format('d M Y') }}</span>
            </div>
            <div class="info-block">
                <span class="label">Heure</span>
                <span class="value">{{ $reservation->created_at->format('H:i') }}</span>
            </div>
            <div class="info-block">
                <span class="label">Véhicule</span>
                <span class="value">{{ $reservation->taxi->matricule }}</span>
            </div>
            <div class="info-block">
                <span class="label">Bagages</span>
                <span class="value">{{ $reservation->bagage ? $reservation->nombre_bagage : 'Standard' }}</span>
            </div>
        </div>

        <div class="seats-strip">
            <span class="label" style="margin:0; margin-right:10px;">Sièges:</span>
            @if($reservation->sieges)
                @foreach($reservation->sieges as $siege)
                    <span class="seat-pill">P{{ $siege }}</span>
                @endforeach
            @else
                <span class="value" style="font-size: 13px;">Placement libre</span>
            @endif
        </div>
    </div>

    <div class="stub">
        <div style="text-align: center;">
            <div class="label" style="color: rgba(255,255,255,0.5);">Total de la réservation</div>
            <div class="price-section">
                <span class="price-large">{{ number_format($reservation->prix_total, 2) }}</span>
                <span class="price-curr">MAD PAYÉ</span>
            </div>
        </div>

        <div>
            <div class="barcode-area">
                <div class="barcode-img"></div>
                <div style="color: black; font-size: 10px; font-weight: 800; margin-top: 5px;">SCANNER POUR EMBARQUER</div>
            </div>
            <div class="ref-code">{{ $reservation->paiement->stripe_payment_intent_id ?? 'GT-'.time() }}</div>
        </div>

        <div style="text-align: center; font-size: 11px; font-weight: 700; letter-spacing: 1px; color: #3b82f6;">
            BON VOYAGE !
        </div>
    </div>
</div>

</body>
</html>
