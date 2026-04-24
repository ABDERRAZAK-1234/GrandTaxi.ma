<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .role-card {
            transition: all 0.2s;
            cursor: pointer;
        }

        .role-card.active {
            border-color: #1d4ed8;
            background: #eff6ff;
        }

        .role-card.active .role-title {
            color: #1d4ed8;
        }

        .role-card.active .radio-circle {
            border-color: #1d4ed8;
            background: #1d4ed8;
        }

        .radio-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .radio-inner {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: white;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md mx-4">

        {{-- LOGO --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 mb-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                    <svg width="20" height="20" fill="white" viewBox="0 0 24 24">
                        <path
                            d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold text-blue-900">GrandTaxi<span class="text-blue-500">.ma</span></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
            <p class="text-gray-500 text-sm mt-1">Choisissez votre type de compte</p>
        </div>

        {{-- ROLE CARDS --}}
        <div class="grid grid-cols-2 gap-4 mb-8">

            {{-- USER --}}
            <div id="card-user" class="role-card active bg-white border-2 border-blue-600 rounded-2xl p-6"
                onclick="selectRole('user')">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <svg width="24" height="24" fill="#1d4ed8" viewBox="0 0 24 24">
                            <path
                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                    <div id="radio-user" class="radio-circle">
                        <div class="radio-inner"></div>
                    </div>
                </div>
                <p id="title-user" class="role-title font-bold text-blue-700 text-base">Je suis client</p>
                <p class="text-gray-400 text-xs mt-1">Réserver des places dans un taxi</p>
            </div>

            {{-- CONDUCTEUR --}}
            <div id="card-driver" class="role-card bg-white border-2 border-gray-200 rounded-2xl p-6"
                onclick="selectRole('driver')">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center">
                        <svg width="24" height="24" fill="#6b7280" viewBox="0 0 24 24">
                            <path
                                d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z" />
                        </svg>
                    </div>

                    <div id="radio-driver" class="radio-circle">
                        <div class="radio-inner"></div>
                    </div>
                </div>

                <p id="title-driver" class="role-title font-bold text-gray-600 text-base">Je suis conducteur</p>
                <p class="text-gray-400 text-xs mt-1">Proposer des trajets en taxi</p>
            </div>

        </div>

        {{-- BOUTON CONTINUER --}}
        <button onclick="continuer()"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition text-base">
            Continuer
        </button>

        <p class="text-center text-sm text-gray-400 mt-5">
            Déjà un compte ?
            <a href="/login" class="text-blue-600 font-semibold hover:underline">Se connecter</a>
        </p>

    </div>

    <script src="{{ asset('js/choose-role.js') }}"></script>

</body>

</html>
