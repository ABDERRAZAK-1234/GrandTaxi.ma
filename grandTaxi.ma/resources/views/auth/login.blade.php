<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-yellow-50 min-h-screen flex items-center justify-center">

<div class="max-w-md w-full mx-4">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-yellow-500">🚕 GrandTaxi.ma</h1>
        <p class="text-gray-500 mt-2">Connectez-vous à votre compte</p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div id="error-msg" class="hidden bg-red-50 border border-red-300 text-red-700 rounded-lg p-3 mb-4 text-sm"></div>

        <div class="space-y-4">
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
            <button onclick="login()"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 rounded-lg transition">
                Se connecter
            </button>
        </div>

        <p class="text-center text-sm text-gray-500 mt-4">
            Pas encore de compte ?
            <a href="/register" class="text-yellow-500 font-semibold hover:underline">S'inscrire</a>
        </p>
    </div>
</div>

<script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
