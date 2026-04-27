<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — GrandTaxi.ma</title>
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
        <p class="mt-1" style="color: var(--gt-text-muted);" id="subtitle">Créez votre compte</p>
    </div>

    {{-- Card --}}
    <div class="gt-card p-8">

        {{-- Role Badge --}}
        <div id="role-badge" class="flex items-center gap-3 mb-6 p-3 rounded-xl" style="background: var(--gt-red-light); border: 1px solid rgba(193,39,45,0.2);">
            <span id="role-icon" class="text-xl"></span>
            <div class="flex-1">
                <p id="role-text" class="text-sm font-bold" style="color: var(--gt-red);"></p>
                <p id="role-sub" class="text-xs" style="color: var(--gt-text-muted);"></p>
            </div>
            <a href="/" class="text-xs font-semibold hover:underline" style="color: var(--gt-red);">Changer</a>
        </div>

        <div id="error-msg" class="hidden gt-alert gt-alert-error mb-4 text-sm whitespace-pre-line"></div>
        <div id="success-msg" class="hidden gt-alert gt-alert-success mb-4 text-sm"></div>

        {{-- Common fields --}}
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="gt-label">Nom</label>
                    <input type="text" id="nom" placeholder="Alami" class="gt-input">
                </div>
                <div>
                    <label class="gt-label">Prénom</label>
                    <input type="text" id="prenom" placeholder="Mohammed" class="gt-input">
                </div>
            </div>
            <div>
                <label class="gt-label">Email</label>
                <input type="email" id="email" placeholder="votre@email.com" class="gt-input">
            </div>
            <div>
                <label class="gt-label">Mot de passe</label>
                <input type="password" id="password" placeholder="••••••••" class="gt-input">
            </div>
            <div>
                <label class="gt-label">Confirmer mot de passe</label>
                <input type="password" id="password_confirmation" placeholder="••••••••" class="gt-input">
            </div>
        </div>

        {{-- Driver fields --}}
        <div id="driver-fields" class="hidden mt-6 space-y-4">
            <div class="flex items-center gap-2 my-3">
                <div class="flex-1 h-px" style="background: var(--gt-border);"></div>
                <span class="text-xs font-semibold uppercase" style="color: var(--gt-gold);">Conducteur</span>
                <div class="flex-1 h-px" style="background: var(--gt-border);"></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="gt-label">CNE</label>
                    <input type="text" id="cne" placeholder="A123456" class="gt-input">
                </div>
                <div>
                    <label class="gt-label">N° Permis</label>
                    <input type="text" id="permis" placeholder="12345678" class="gt-input">
                </div>
            </div>

            <div class="flex items-center gap-2 my-3">
                <div class="flex-1 h-px" style="background: var(--gt-border);"></div>
                <span class="text-xs font-semibold uppercase" style="color: var(--gt-gold);">Taxi</span>
                <div class="flex-1 h-px" style="background: var(--gt-border);"></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="gt-label">Matricule</label>
                    <input type="text" id="taxi_matricule" placeholder="123-A-45" class="gt-input">
                </div>
                <div>
                    <label class="gt-label">Capacité</label>
                    <select id="taxi_capacite" class="gt-input">
                        <option value="4">4 places</option>
                        <option value="5">5 places</option>
                        <option value="6" selected>6 places</option>
                    </select>
                </div>
                
                <div class="col-span-2 mt-2">
                    <label class="gt-label">Trajet (Route)</label>
                    <select id="taxi_trajet" class="gt-input">
                        <option value="">Sélectionnez un trajet</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="gt-label">Photo du taxi <span style="color: var(--gt-red);">*</span></label>
                <input type="file" id="taxi_image" accept="image/jpeg,image/png,image/jpg,image/webp" required
                    class="gt-input text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-white file:text-xs file:font-semibold" style="color: var(--gt-text-muted); --gt-file-bg: var(--gt-red);">
                <p class="text-xs mt-1" style="color: var(--gt-text-muted);">Formats acceptés : jpeg, png, jpg, webp. Max 2 Mo.</p>
            </div>
        </div>

        <button onclick="register()" class="gt-btn gt-btn-primary gt-btn-full gt-btn-lg mt-6">
            S'inscrire
        </button>

        <p class="text-center text-sm mt-4" style="color: var(--gt-text-muted);">
            Déjà un compte ?
            <a href="/login" class="font-semibold hover:underline" style="color: var(--gt-red);">Se connecter</a>
        </p>
    </div>
</div>

<script src="{{ asset('js/register.js') }}"></script>
</body>
</html>
