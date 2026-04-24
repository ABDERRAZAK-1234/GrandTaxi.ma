<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrandTaxi.ma | Réservation de Taxis Intercités</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .siege {
            cursor: pointer;
            transition: all 0.2s;
            fill: #dbeafe;
            stroke: #3b82f6;
            stroke-width: 2;
        }

        .siege.taken {
            fill: #fecaca;
            stroke: #ef4444;
            cursor: not-allowed;
        }

        .siege.selected {
            fill: #2563eb;
            stroke: #1e40af;
        }

        .siege:hover:not(.taken) {
            fill: #93c5fd;
        }

        .siege-label {
            font-size: 13px;
            font-weight: bold;
            pointer-events: none;
            fill: #1e3a8a;
        }

        .siege-label.selected {
            fill: white;
        }

        .siege-label.taken {
            fill: #991b1b;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

    <nav class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
        <div class="container mx-auto px-4 md:px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center">
                    <i class="fas fa-taxi text-white"></i>
                </div>
                <span class="text-xl font-bold tracking-tight text-blue-900">GrandTaxi<span
                        class="text-blue-500">.ma</span></span>
            </div>

            <div class="hidden md:flex items-center space-x-8 font-medium text-gray-600">
                <a href="/index" class="hover:text-blue-600 transition">Accueil</a>
                <a href="#trajets" class="hover:text-blue-600 transition">Trajets</a>

                <div id="nav-guest" class="flex items-center gap-4">
                    <a href="/login" class="hover:text-blue-600 transition">Connexion</a>
                    <a href="/register"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-full transition shadow-md">S'inscrire</a>
                </div>

                <div id="nav-user" class="hidden items-center gap-6">
                    <a href="/mes-reservations" class="hover:text-blue-600 transition">Mes réservations</a>
                    <div class="flex items-center gap-3 border-l pl-6 border-gray-200">
                        <span id="nav-username" class="text-gray-700 font-semibold text-sm"></span>
                        <button onclick="logout()"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full transition text-xs font-bold uppercase tracking-wider">
                            Déconnexion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <section class="relative py-16 md:py-28 overflow-hidden">
        {{-- <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1531266752426-aad4966a9238?auto=format&fit=crop&q=80&w=2000"
                class="w-full h-full object-cover opacity-10" alt="Maroc">
        </div> --}}
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-blue-900 mb-6 leading-tight">
                Réservez votre place dans un <br class="hidden md:block">
                <span class="text-blue-600">Grand Taxi Intercité</span>
            </h1>
            <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">
                La façon la plus rapide de voyager entre les villes. Réservez votre siège instantanément et voyagez en
                toute sécurité.
            </p>
            <div class="max-w-4xl mx-auto glass-effect p-4 md:p-6 rounded-2xl shadow-2xl border border-white">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative text-left">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Départ</label>
                        <div class="relative">
                            <i class="fas fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-blue-500"></i>
                            <select id="ville-depart"
                                class="w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                                <option value="">Ville de départ</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative text-left">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Arrivée</label>
                        <div class="relative">
                            <i class="fas fa-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-blue-600"></i>
                            <select id="ville-arrivee"
                                class="w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                                <option value="">Ville d'arrivée</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button onclick="rechercherTrajets()"
                            class="w-full gradient-bg text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-blue-200 transition transform hover:scale-[1.02] active:scale-95 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i> Rechercher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="trajets" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-2xl font-bold text-gray-800">Taxis disponibles</h2>
                <span id="search-label"
                    class="text-sm text-blue-600 font-semibold bg-blue-50 px-4 py-1.5 rounded-full hidden"></span>
            </div>
            <div id="loading" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                <p class="text-gray-500 mt-3">Recherche en cours...</p>
            </div>
            <div id="empty-state" class="text-center py-16">
                <i class="fas fa-route text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-400 text-lg">Sélectionnez une ville de départ et d'arrivée pour voir les trajets
                    disponibles.</p>
            </div>
            <div id="no-results" class="hidden text-center py-16">
                <i class="fas fa-ban text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-400 text-lg">Aucun trajet disponible pour ce trajet.</p>
            </div>
            <div id="trajets-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 hidden"></div>
        </div>
    </section>

    {{-- MODAL RESERVATION AVEC PLAN DU TAXI --}}
    <div id="modal-reservation" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">

            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Choisir vos sièges</h3>
                    <p id="modal-trajet-label" class="text-sm text-blue-600 font-medium mt-1"></p>
                </div>
                <button onclick="fermerModal()"
                    class="text-gray-400 hover:text-gray-600 text-2xl w-8 h-8 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="modal-error"
                class="hidden bg-red-50 border border-red-300 text-red-700 rounded-lg p-3 mb-4 text-sm"></div>
            <div id="modal-success"
                class="hidden bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-4 text-sm"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- PLAN DU TAXI --}}
                <div class="relative w-full max-w-sm mx-auto aspect-[220/480]">

                    <!-- Image -->
                    <img src="{{ asset('images/taxi_kbir_booking_seats-Photoroom12.png') }}"
                        class="absolute inset-0 w-full h-full object-contain rounded-xl" alt="Plan du taxi">

                    <!-- SVG -->
                    <svg viewBox="0 0 220 480" preserveAspectRatio="xMidYMid meet"
                        class="absolute inset-0 w-full h-full">

                        <!-- S1 -->
                        <rect id="s1" class="siege" x="133" y="185" width="25" height="30"
                            rx="10" onclick="toggleSiege('s1',1)" />
                        <text id="s1-label" class="siege-label" x="145" y="205" text-anchor="middle">S1</text>

                        <!-- S2 -->
                        <rect id="s2" class="siege" x="63" y="260" width="25" height="30"
                            rx="10" onclick="toggleSiege('s2',2)" />
                        <text id="s2-label" class="siege-label" x="76" y="280" text-anchor="middle">S2</text>

                        <!-- S3 -->
                        <rect id="s3" class="siege" x="99" y="260" width="25" height="30"
                            rx="10" onclick="toggleSiege('s3',3)" />
                        <text id="s3-label" class="siege-label" x="111" y="280" text-anchor="middle">S3</text>

                        <!-- S4 -->
                        <rect id="s4" class="siege" x="135" y="260" width="25" height="30"
                            rx="10" onclick="toggleSiege('s4',4)" />
                        <text id="s4-label" class="siege-label" x="147" y="280" text-anchor="middle">S4</text>

                        <!-- S5 -->
                        <rect id="s5" class="siege" x="73" y="340" width="25" height="30"
                            rx="10" onclick="toggleSiege('s5',5)" />
                        <text id="s5-label" class="siege-label" x="85" y="360" text-anchor="middle">S5</text>

                        <!-- S6 -->
                        <rect id="s6" class="siege" x="124" y="340" width="25" height="30"
                            rx="10" onclick="toggleSiege('s6',6)" />
                        <text id="s6-label" class="siege-label" x="137" y="360" text-anchor="middle">S6</text>

                    </svg>
                </div>

                {{-- PANEL DROIT --}}
                <div class="flex flex-col gap-3">

                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Légende</p>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-blue-100 border-2 border-blue-500 rounded flex-shrink-0"></div>
                                <span class="text-sm text-gray-600">Disponible</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-blue-600 border-2 border-blue-800 rounded flex-shrink-0"></div>
                                <span class="text-sm text-gray-600">Sélectionné</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-red-200 border-2 border-red-500 rounded flex-shrink-0"></div>
                                <span class="text-sm text-gray-600">Occupé</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-1">Sièges choisis</p>
                        <p id="sieges-display" class="text-xl font-bold text-gray-800">Aucun</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taxi</label>
                        <select id="select-taxi"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
                            <option value="">Chargement...</option>
                        </select>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="bagage" class="w-4 h-4 text-blue-600">
                            <span class="text-sm font-medium text-gray-700">Bagages (+10 MAD/bagage)</span>
                        </label>
                        <div id="bagage-count" class="hidden mt-2">
                            <input type="number" id="nombre-bagage" min="1" max="2" value="1"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1"><span id="modal-prix"></span> MAD/place</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mb-1">Total estimé</p>
                        <p id="prix-total" class="text-3xl font-bold text-blue-600">0 MAD</p>
                    </div>

                    <button id="btn-payer" onclick="initierPaiement()" disabled
                        class="w-full gradient-bg text-white font-bold py-3 rounded-xl transition hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-credit-card"></i> Payer & Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-blue-900 mb-16">Comment ça marche ?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div>
                    <div
                        class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-route text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Choisir le trajet</h3>
                    <p class="text-gray-500">Sélectionnez votre ville de départ et d'arrivée au Maroc.</p>
                </div>
                <div>
                    <div
                        class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chair text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Choisir vos sièges</h3>
                    <p class="text-gray-500">Cliquez sur les sièges disponibles dans le plan du taxi.</p>
                </div>
                <div>
                    <div
                        class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-ticket text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Payer & Partir</h3>
                    <p class="text-gray-500">Paiement sécurisé par carte et confirmation instantanée.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                    <i class="fas fa-taxi text-white text-xs"></i>
                </div>
                <span class="text-xl font-bold text-white">GrandTaxi.ma</span>
            </div>
            <p class="text-sm">© 2026 GrandTaxi.ma — Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        Pusher.logToConsole = false;
        window.pusherInstance = new Pusher('rtotagzg2tbikodaqnzr', {
            wsHost: 'localhost',
            wsPort: 8080,
            forceTLS: false,
            enableStats: false,
            enabledTransports: ['ws'],
            cluster: 'mt1',
        });
        window.pusherInstance.connection.bind('connected', () => {
            console.log('✅ Reverb connecté !');
        });
    </script>

    <script src="{{ asset('js/index.js') }}"></script>
</body>

</html>
