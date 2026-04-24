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
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        #card-element {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 14px;
            background: white;
        }
    </style>
</head>
<body class="bg-blue-50 min-h-screen flex items-center justify-center">

<div class="max-w-md w-full mx-4">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-taxi text-white text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-blue-900">GrandTaxi.ma</h1>
        <p class="text-gray-500 mt-1">Paiement sécurisé</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-lg p-8">

        {{-- Montant --}}
        <div class="bg-blue-50 rounded-xl p-4 mb-6 text-center">
            <p class="text-sm text-gray-500 mb-1">Montant à payer</p>
            <p class="text-4xl font-bold text-blue-600" id="montant-display">-- MAD</p>
        </div>

        {{-- Erreur / Succès --}}
        <div id="error-msg" class="hidden bg-red-50 border border-red-300 text-red-700 rounded-lg p-3 mb-4 text-sm"></div>
        <div id="success-msg" class="hidden bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-4 text-sm"></div>

        {{-- Stripe Card Element --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-credit-card mr-2 text-blue-500"></i>Informations de carte
            </label>
            <div id="card-element"></div>
            <div id="card-errors" class="text-red-500 text-sm mt-2"></div>
        </div>

        {{-- Carte de test --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
            <p class="text-xs text-yellow-700 font-semibold mb-1">🧪 Carte de test Stripe :</p>
            <p class="text-xs text-yellow-600 font-mono">4242 4242 4242 4242 — 12/26 — 123</p>
        </div>

        {{-- Bouton payer --}}
        <button id="btn-payer" onclick="payerStripe()"
            class="w-full gradient-bg text-white font-bold py-4 rounded-xl transition hover:opacity-90 flex items-center justify-center gap-2">
            <i class="fas fa-lock"></i>
            <span id="btn-text">Payer maintenant</span>
        </button>

        <a href="/index" class="block text-center text-sm text-gray-400 hover:text-gray-600 mt-4">
            ← Annuler et retourner
        </a>
    </div>

    {{-- Sécurité --}}
    <p class="text-center text-xs text-gray-400 mt-4">
        <i class="fas fa-lock mr-1"></i> Paiement sécurisé par Stripe
    </p>
</div>
<script>
    window.STRIPE_KEY = "{{ config('services.stripe.key') }}";
</script>

<script src="{{ asset('js/paiement.js') }}"></script>
</body>
</html>
