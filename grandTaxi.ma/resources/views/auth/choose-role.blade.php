<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/grandtaxi.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .role-card { transition: all 0.2s; cursor: pointer; }
        .role-card.active { border-color: var(--gt-red) !important; background: var(--gt-red-light); }
        .role-card.active .role-title { color: var(--gt-red); }
        .role-card.active .radio-circle { border-color: var(--gt-red); background: var(--gt-red); }
        .radio-circle {
            width: 22px; height: 22px; border-radius: 50%;
            border: 2px solid var(--gt-border); background: white;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .radio-inner { width: 10px; height: 10px; border-radius: 50%; background: white; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background: var(--gt-bg-light);">

<div class="w-full max-w-md mx-4">

    {{-- Logo --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 mb-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center gt-gradient shadow-lg">
                <i class="fas fa-taxi text-white text-lg"></i>
            </div>
        </div>
        <h1 class="text-2xl font-extrabold" style="color: var(--gt-text);">
            Grand<span style="color: var(--gt-red);">Taxi</span>.ma
        </h1>
        <p class="text-sm mt-1" style="color: var(--gt-text-muted);">Choisissez votre type de compte</p>
    </div>

    {{-- Role Cards --}}
    <div class="grid grid-cols-2 gap-4 mb-8">

        {{-- User --}}
        <div id="card-user" class="role-card active gt-card p-6" style="border: 2px solid var(--gt-red);"
            onclick="selectRole('user')">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: var(--gt-red-light);">
                    <i class="fas fa-user text-lg" style="color: var(--gt-red);"></i>
                </div>
                <div id="radio-user" class="radio-circle">
                    <div class="radio-inner"></div>
                </div>
            </div>
            <p id="title-user" class="role-title font-bold text-base" style="color: var(--gt-red);">Je suis client</p>
            <p class="text-xs mt-1" style="color: var(--gt-text-light);">Réserver des places dans un taxi</p>
        </div>

        {{-- Conducteur --}}
        <div id="card-driver" class="role-card gt-card p-6" style="border: 2px solid var(--gt-border);"
            onclick="selectRole('driver')">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: var(--gt-border-light);">
                    <i class="fas fa-taxi text-lg" style="color: var(--gt-text-muted);"></i>
                </div>
                <div id="radio-driver" class="radio-circle">
                    <div class="radio-inner"></div>
                </div>
            </div>
            <p id="title-driver" class="role-title font-bold text-base" style="color: var(--gt-text-muted);">Je suis conducteur</p>
            <p class="text-xs mt-1" style="color: var(--gt-text-light);">Proposer des trajets en taxi</p>
        </div>
    </div>

    {{-- Continue --}}
    <button onclick="continuer()" class="gt-btn gt-btn-primary gt-btn-full gt-btn-lg">
        Continuer
    </button>

    <p class="text-center text-sm mt-5" style="color: var(--gt-text-light);">
        Déjà un compte ?
        <a href="/login" class="font-semibold hover:underline" style="color: var(--gt-red);">Se connecter</a>
    </p>
</div>

<script src="{{ asset('js/choose-role.js') }}"></script>
</body>
</html>
