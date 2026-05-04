<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrandTaxi.ma | Réservation de Taxis Intercités</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/grandtaxi.css') }}">
</head>
<body style="background: var(--gt-bg-light); color: var(--gt-text);">

    {{-- ═══ NAVBAR ═══ --}}
    <nav class="sticky top-0 z-50 bg-white border-b shadow-sm" style="border-color: var(--gt-border-light);">
        <div class="container mx-auto px-4 md:px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 gt-gradient rounded-lg flex items-center justify-center">
                    <i class="fas fa-taxi text-white"></i>
                </div>
                <span class="text-xl font-bold tracking-tight" style="color: var(--gt-text);">
                    Grand<span style="color: var(--gt-red);">Taxi</span><span class="text-gray-400">.ma</span>
                </span>
            </div>

            <div class="hidden md:flex items-center space-x-8 font-medium" style="color: var(--gt-text-muted);">
                <a href="/index" class="hover:text-gray-900 transition">Accueil</a>
                <a href="#trajets" class="hover:text-gray-900 transition">Trajets</a>

                <div id="nav-guest" class="flex items-center gap-4">
                    <a href="/login" class="hover:text-gray-900 transition">Connexion</a>
                    <a href="/register" class="gt-btn gt-btn-primary gt-btn-sm" style="border-radius: 9999px;">S'inscrire</a>
                </div>

                <div id="nav-user" class="hidden items-center gap-6">
                    <a href="/mes-reservations" class="hover:text-gray-900 transition">Mes réservations</a>
                    <div class="flex items-center gap-3 border-l pl-6" style="border-color: var(--gt-border);">
                        <span id="nav-username" class="font-semibold text-sm" style="color: var(--gt-text);"></span>
                        <button onclick="logout()" class="gt-btn gt-btn-danger gt-btn-sm" style="border-radius: 9999px; font-size: 11px;">
                            Déconnexion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- ═══ HERO ═══ --}}
    <section class="relative py-16 md:py-28 overflow-hidden">
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight" style="color: var(--gt-text);">
                Réservez votre place dans un <br class="hidden md:block">
                <span style="color: var(--gt-red);">Grand Taxi Intercité</span>
            </h1>
            <p class="text-lg mb-12 max-w-2xl mx-auto" style="color: var(--gt-text-muted);">
                La façon la plus rapide de voyager entre les villes. Réservez votre siège instantanément et voyagez en toute sécurité.
            </p>

            {{-- Search Bar --}}
            <div class="max-w-4xl mx-auto bg-white p-4 md:p-6 rounded-2xl shadow-2xl border" style="border-color: var(--gt-border-light);">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative text-left">
                        <label class="gt-label ml-1">Départ</label>
                        <div class="relative">
                            <i class="fas fa-location-dot absolute left-4 top-1/2 -translate-y-1/2" style="color: var(--gt-red);"></i>
                            <select id="ville-depart" class="gt-input pl-11 py-4 cursor-pointer">
                                <option value="">Ville de départ</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative text-left">
                        <label class="gt-label ml-1">Arrivée</label>
                        <div class="relative">
                            <i class="fas fa-map-pin absolute left-4 top-1/2 -translate-y-1/2" style="color: var(--gt-green);"></i>
                            <select id="ville-arrivee" class="gt-input pl-11 py-4 cursor-pointer">
                                <option value="">Ville d'arrivée</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button onclick="rechercherTrajets()" class="gt-btn gt-btn-primary gt-btn-full gt-btn-lg shadow-lg">
                            <i class="fas fa-search mr-2"></i> Rechercher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ TAXIS ═══ --}}
    <section id="trajets" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-2xl font-bold" style="color: var(--gt-text);">Taxis disponibles</h2>
                <span id="search-label" class="text-sm font-semibold px-4 py-1.5 rounded-full hidden gt-badge-red-light"></span>
            </div>
            <div id="loading" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl" style="color: var(--gt-red);"></i>
                <p class="mt-3" style="color: var(--gt-text-muted);">Recherche en cours...</p>
            </div>
            <div id="empty-state" class="text-center py-16">
                <i class="fas fa-route text-5xl text-gray-300 mb-4"></i>
                <p class="text-lg" style="color: var(--gt-text-light);">Sélectionnez une ville de départ et d'arrivée pour voir les trajets disponibles.</p>
            </div>
            <div id="no-results" class="hidden text-center py-16">
                <i class="fas fa-ban text-5xl text-gray-300 mb-4"></i>
                <p class="text-lg" style="color: var(--gt-text-light);">Aucun trajet disponible pour ce trajet.</p>
            </div>
            <div id="trajets-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 hidden"></div>
        </div>
    </section>

    {{-- ═══ MODAL RESERVATION ═══ --}}
    <div id="modal-reservation" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">

            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-xl font-bold" style="color: var(--gt-text);">Choisir vos sièges</h3>
                    <p id="modal-trajet-label" class="text-sm font-medium mt-1" style="color: var(--gt-red);"></p>
                </div>
                <button onclick="fermerModal()" class="text-gray-400 hover:text-gray-600 text-2xl w-8 h-8 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="modal-error" class="hidden gt-alert gt-alert-error mb-4 text-sm"></div>
            <div id="modal-success" class="hidden gt-alert gt-alert-success mb-4 text-sm"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- PLAN DU TAXI --}}
                <div class="relative w-full max-w-sm mx-auto aspect-[220/480]">
                    <img src="{{ asset('images/taxi_kbir_booking_seats-Photoroom12.png') }}"
                        class="absolute inset-0 w-full h-full object-contain rounded-xl" alt="Plan du taxi">
                    <svg viewBox="0 0 220 480" preserveAspectRatio="xMidYMid meet" class="absolute inset-0 w-full h-full">
                        <rect id="s1" class="siege" x="133" y="185" width="25" height="30" rx="10" onclick="toggleSiege('s1',1)" />
                        <text id="s1-label" class="siege-label" x="145" y="205" text-anchor="middle">S1</text>
                        <rect id="s2" class="siege" x="63" y="260" width="25" height="30" rx="10" onclick="toggleSiege('s2',2)" />
                        <text id="s2-label" class="siege-label" x="76" y="280" text-anchor="middle">S2</text>
                        <rect id="s3" class="siege" x="99" y="260" width="25" height="30" rx="10" onclick="toggleSiege('s3',3)" />
                        <text id="s3-label" class="siege-label" x="111" y="280" text-anchor="middle">S3</text>
                        <rect id="s4" class="siege" x="135" y="260" width="25" height="30" rx="10" onclick="toggleSiege('s4',4)" />
                        <text id="s4-label" class="siege-label" x="147" y="280" text-anchor="middle">S4</text>
                        <rect id="s5" class="siege" x="73" y="340" width="25" height="30" rx="10" onclick="toggleSiege('s5',5)" />
                        <text id="s5-label" class="siege-label" x="85" y="360" text-anchor="middle">S5</text>
                        <rect id="s6" class="siege" x="124" y="340" width="25" height="30" rx="10" onclick="toggleSiege('s6',6)" />
                        <text id="s6-label" class="siege-label" x="137" y="360" text-anchor="middle">S6</text>
                    </svg>
                </div>

                {{-- PANEL DROIT --}}
                <div class="flex flex-col gap-3">
                    <div class="rounded-xl p-3" style="background: var(--gt-bg-light);">
                        <p class="gt-label">Légende</p>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded flex-shrink-0" style="background: #FEF2F2; border: 2px solid var(--gt-red);"></div>
                                <span class="text-sm" style="color: var(--gt-text-muted);">Disponible</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded flex-shrink-0" style="background: var(--gt-red); border: 2px solid var(--gt-red-hover);"></div>
                                <span class="text-sm" style="color: var(--gt-text-muted);">Sélectionné</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded flex-shrink-0" style="background: #D1D5DB; border: 2px solid #9CA3AF;"></div>
                                <span class="text-sm" style="color: var(--gt-text-muted);">Occupé</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl p-3" style="background: var(--gt-bg-light);">
                        <p class="gt-label">Sièges choisis</p>
                        <p id="sieges-display" class="text-xl font-bold" style="color: var(--gt-text);">Aucun</p>
                    </div>

                    <div>
                        <label class="gt-label">Taxi</label>
                        <select id="select-taxi" class="gt-input text-sm">
                            <option value="">Chargement...</option>
                        </select>
                    </div>

                    <div class="rounded-xl p-3" style="background: var(--gt-bg-light);">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="bagage" class="w-4 h-4 accent-[#C1272D]">
                            <span class="text-sm font-medium" style="color: var(--gt-text);">Bagages (+10 MAD/bagage)</span>
                        </label>
                        <div id="bagage-count" class="hidden mt-2">
                            <input type="number" id="nombre-bagage" min="1" max="2" value="1" class="gt-input text-sm">
                        </div>
                    </div>

                    <div class="rounded-xl p-3" style="background: var(--gt-red-light);">
                        <p class="text-xs mb-1" style="color: var(--gt-text-muted);"><span id="modal-prix"></span> MAD/place</p>
                        <p class="gt-label">Total estimé</p>
                        <p id="prix-total" class="text-3xl font-bold" style="color: var(--gt-red);">0 MAD</p>
                    </div>

                    <button id="btn-payer" onclick="initierPaiement()" disabled
                        class="gt-btn gt-btn-primary gt-btn-full gt-btn-lg disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-credit-card"></i> Payer & Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ HOW IT WORKS ═══ --}}
    <section class="py-20" style="background: var(--gt-bg-light);">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-16" style="color: var(--gt-text);">Comment ça marche ?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div>
                    <div class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-route text-3xl" style="color: var(--gt-red);"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Choisir le trajet</h3>
                    <p style="color: var(--gt-text-muted);">Sélectionnez votre ville de départ et d'arrivée au Maroc.</p>
                </div>
                <div>
                    <div class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chair text-3xl" style="color: var(--gt-green);"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Choisir vos sièges</h3>
                    <p style="color: var(--gt-text-muted);">Cliquez sur les sièges disponibles dans le plan du taxi.</p>
                </div>
                <div>
                    <div class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-ticket text-3xl" style="color: var(--gt-gold);"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Payer & Partir</h3>
                    <p style="color: var(--gt-text-muted);">Paiement sécurisé par carte et confirmation instantanée.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ FOOTER ═══ --}}
    <footer class="py-12" style="background: var(--gt-bg-dark); color: var(--gt-text-dark-muted);">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="w-8 h-8 gt-gradient rounded flex items-center justify-center">
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
        window.pusherInstance = new Pusher('{{ env("REVERB_APP_KEY", "rtotagzg2tbikodaqnzr") }}', {
            wsHost: '{{ env("REVERB_HOST", "localhost") }}',
            wsPort: {{ env("REVERB_PORT", 8080) }},
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
