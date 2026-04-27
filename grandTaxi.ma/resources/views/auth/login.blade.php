<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/grandtaxi.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center py-8" style="background: var(--gt-bg-light);">

<div class="max-w-md w-full mx-4">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 mb-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center gt-gradient shadow-lg">
                <i class="fas fa-taxi text-white text-lg"></i>
            </div>
        </div>
        <h1 class="text-2xl font-extrabold" style="color: var(--gt-text);">
            Grand<span style="color: var(--gt-red);">Taxi</span>.ma
        </h1>
        <p class="mt-1" style="color: var(--gt-text-muted);">Connectez-vous à votre compte</p>
    </div>

    {{-- Card --}}
    <div class="gt-card p-8">

        <div id="error-msg" class="hidden gt-alert gt-alert-error mb-4 text-sm"></div>

        <div class="space-y-4">
            <div>
                <label class="gt-label">Email</label>
                <input type="email" id="email" placeholder="votre@email.com" class="gt-input">
            </div>
            <div>
                <label class="gt-label">Mot de passe</label>
                <input type="password" id="password" placeholder="••••••••" class="gt-input">
            </div>
            <button onclick="login()" class="gt-btn gt-btn-primary gt-btn-full gt-btn-lg">
                Se connecter
            </button>
        </div>

        <p class="text-center text-sm mt-4" style="color: var(--gt-text-muted);">
            Pas encore de compte ?
            <a href="/register" class="font-semibold hover:underline" style="color: var(--gt-red);">S'inscrire</a>
        </p>
    </div>
</div>

<script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
