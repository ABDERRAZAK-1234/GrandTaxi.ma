<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="bg-yellow-50 min-h-screen flex items-center justify-center py-8">

<div class="max-w-md w-full mx-4">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-yellow-500">🚕 GrandTaxi.ma</h1>
        <p class="text-gray-500 mt-2" id="subtitle">Créez votre compte</p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8">

        <!-- Role Badge -->
        <div id="role-badge" class="flex items-center gap-2 mb-6 p-3 rounded-xl bg-yellow-100 border border-yellow-200">
            <span id="role-icon" class="text-xl"></span>
            <div>
                <p id="role-text" class="text-sm font-bold text-yellow-700"></p>
                <p id="role-sub" class="text-xs text-yellow-500"></p>
            </div>
            <a href="/" class="ml-auto text-xs text-yellow-600 hover:underline">Changer</a>
        </div>

        <div id="error-msg" class="hidden bg-red-50 border border-red-300 text-red-700 rounded-lg p-3 mb-4 text-sm whitespace-pre-line"></div>
        <div id="success-msg" class="hidden bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-4 text-sm"></div>

        <!-- Champs communs -->
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="nom" placeholder="Alami"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" id="prenom" placeholder="Mohammed"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" placeholder="votre@email.com"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" id="password" placeholder="••••••••"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer mot de passe</label>
                <input type="password" id="password_confirmation" placeholder="••••••••"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
        </div>

        <!-- Champs Conducteur -->
        <div id="driver-fields" class="hidden mt-6 space-y-4">

            <div class="flex items-center gap-2 my-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs font-semibold text-gray-400 uppercase">Conducteur</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNE</label>
                    <input type="text" id="cne" placeholder="A123456"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° Permis</label>
                    <input type="text" id="permis" placeholder="12345678"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>

            <div class="flex items-center gap-2 my-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs font-semibold text-gray-400 uppercase">Taxi</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Matricule</label>
                    <input type="text" id="taxi_matricule" placeholder="123-A-45"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacité</label>
                    <select id="taxi_capacite"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="4">4 places</option>
                        <option value="5">5 places</option>
                        <option value="6" selected>6 places</option>
                    </select>
                </div>
            </div>
        </div>

        <button onclick="register()"
            class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 rounded-lg transition mt-6">
            S'inscrire
        </button>

        <p class="text-center text-sm text-gray-500 mt-4">
            Déjà un compte ?
            <a href="/login" class="text-yellow-500 font-semibold hover:underline">Se connecter</a>
        </p>

    </div>
</div>

<script src="{{ asset('js/register.js') }}"></script>
</body>
</html>
