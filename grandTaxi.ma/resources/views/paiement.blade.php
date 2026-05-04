<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/grandtaxi.css') }}">
    <style>
        #card-element {
            border: 1px solid var(--gt-border);
            border-radius: var(--gt-radius);
            padding: 14px;
            background: white;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background: var(--gt-bg-light);">

<div class="max-w-md w-full mx-4">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 gt-gradient rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <i class="fas fa-taxi text-white text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold" style="color: var(--gt-text);">
            Grand<span style="color: var(--gt-red);">Taxi</span>.ma
        </h1>
        <p class="mt-1" style="color: var(--gt-text-muted);">Paiement sécurisé</p>
    </div>

    {{-- Card --}}
    <div class="gt-card p-8">

        {{-- Montant --}}
        <div class="rounded-xl p-4 mb-6 text-center" style="background: var(--gt-red-light);">
            <p class="text-sm mb-1" style="color: var(--gt-text-muted);">Montant à payer</p>
            <p class="text-4xl font-bold" style="color: var(--gt-red);" id="montant-display">-- MAD</p>
        </div>

        {{-- Erreur / Succès --}}
        <div id="error-msg" class="hidden gt-alert gt-alert-error mb-4 text-sm"></div>
        <div id="success-msg" class="hidden gt-alert gt-alert-success mb-4 text-sm"></div>

        {{-- Stripe Card --}}
        <div class="mb-6">
            <label class="gt-label">
                <i class="fas fa-credit-card mr-2" style="color: var(--gt-red);"></i>Informations de carte
            </label>
            <div id="card-element"></div>
            <div id="card-errors" class="text-sm mt-2" style="color: var(--gt-red);"></div>
        </div>

        {{-- Test card notice --}}
        <div class="gt-alert gt-alert-warning mb-6">
            <p class="text-xs font-semibold mb-1">🧪 Carte de test Stripe :</p>
            <p class="text-xs font-mono">4242 4242 4242 4242 — 12/26 — 123</p>
        </div>

        {{-- Pay Button --}}
        <button id="btn-payer" onclick="payerStripe()"
            class="gt-btn gt-btn-primary gt-btn-full gt-btn-lg">
            <i class="fas fa-lock"></i>
            <span id="btn-text">Payer maintenant</span>
        </button>

        <a href="/index" class="block text-center text-sm mt-4 hover:underline" style="color: var(--gt-text-light);">
            ← Annuler et retourner
        </a>
    </div>

    <p class="text-center text-xs mt-4" style="color: var(--gt-text-light);">
        <i class="fas fa-lock mr-1"></i> Paiement sécurisé par Stripe
    </p>
</div>

<script>
    window.STRIPE_KEY = "{{ config('services.stripe.key') }}";
</script>
<script src="{{ asset('js/paiement.js') }}"></script>
</body>
</html>
