<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #0f1117; color: #f1f5f9; }

        .sidebar { background: #161b27; border-right: 1px solid #1e2537; }
        .nav-item { transition: all 0.2s; border-radius: 10px; }
        .nav-item:hover { background: #1e2537; }
        .nav-item.active { background: #1d4ed8; }
        .nav-item.active span { color: white; }

        .card { background: #161b27; border: 1px solid #1e2537; border-radius: 16px; }
        .card-hover { transition: all 0.2s; }
        .card-hover:hover { border-color: #1d4ed8; transform: translateY(-2px); }

        .stat-card { background: linear-gradient(135deg, #1e2537, #161b27); border: 1px solid #1e2537; border-radius: 16px; }

        .badge { border-radius: 20px; font-size: 11px; font-weight: 700; padding: 3px 10px; }
        .badge-green  { background: #052e16; color: #4ade80; }
        .badge-blue   { background: #0c1a3d; color: #60a5fa; }
        .badge-red    { background: #2d0a0a; color: #f87171; }
        .badge-yellow { background: #2d1f00; color: #fbbf24; }
        .badge-purple { background: #1e0a3d; color: #c084fc; }
        .badge-cyan { background: #082f49; color: #22d3ee; }
        .badge-gray { background: #1e293b; color: #94a3b8; }

        .table-row { border-bottom: 1px solid #1e2537; transition: background 0.15s; }
        .table-row:hover { background: #1e2537; }
        .table-row:last-child { border-bottom: none; }

        .tab { transition: all 0.2s; border-bottom: 2px solid transparent; }
        .tab.active { border-bottom-color: #1d4ed8; color: #60a5fa; }
        .tab:hover:not(.active) { color: #94a3b8; }

        .btn-primary { background: #1d4ed8; color: white; border-radius: 10px; transition: all 0.2s; }
        .btn-primary:hover { background: #1e40af; }
        .btn-danger { background: #7f1d1d; color: #fca5a5; border-radius: 10px; transition: all 0.2s; }
        .btn-danger:hover { background: #991b1b; }

        .input-dark { background: #0f1117; border: 1px solid #1e2537; border-radius: 10px; color: #f1f5f9; }
        .input-dark:focus { outline: none; border-color: #1d4ed8; }

        .modal-overlay { background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #0f1117; }
        ::-webkit-scrollbar-thumb { background: #1e2537; border-radius: 4px; }

        .pulse-dot { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .skeleton { background: linear-gradient(90deg, #1e2537 25%, #252d3d 50%, #1e2537 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 8px; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    <aside class="sidebar w-64 flex-shrink-0 flex flex-col h-full">

        {{-- Logo --}}
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-taxi text-white text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-white text-sm">GrandTaxi.ma</p>
                    <p class="text-xs text-gray-500">Administration</p>
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <p class="text-xs font-bold text-gray-600 uppercase tracking-wider px-3 mb-3">Principal</p>

            <button onclick="showTab('dashboard')" id="nav-dashboard"
                class="nav-item active w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-chart-pie w-4 text-center text-blue-300"></i>
                <span class="text-sm font-medium text-white">Dashboard</span>
            </button>

            <button onclick="showTab('reservations')" id="nav-reservations"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-ticket w-4 text-center text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Réservations</span>
                <span id="badge-reservations" class="ml-auto badge badge-blue">0</span>
            </button>

            <button onclick="showTab('trajets')" id="nav-trajets"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-route w-4 text-center text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Trajets</span>
            </button>

            <button onclick="showTab('taxis')" id="nav-taxis"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-taxi w-4 text-center text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Taxis</span>
            </button>

            <button onclick="showTab('villes')" id="nav-villes"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-city w-4 text-center text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Villes</span>
            </button>

            <button onclick="showTab('users')" id="nav-users"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-users w-4 text-center text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Utilisateurs</span>
            </button>

            <button onclick="showTab('paiements')" id="nav-paiements"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-left">
                <i class="fas fa-credit-card w-4 text-center text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Paiements</span>
            </button>
        </nav>

        {{-- Admin user --}}
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold text-white" id="admin-avatar">A</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate" id="admin-name">Admin</p>
                    <p class="text-xs text-gray-500">Super Admin</p>
                </div>
                <button onclick="logout()" class="text-gray-500 hover:text-red-400 transition">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 overflow-y-auto">

        {{-- TOPBAR --}}
        <div class="sticky top-0 z-10 px-8 py-4 border-b border-gray-800 flex items-center justify-between" style="background:#0f1117">
            <div>
                <h1 class="text-xl font-bold text-white" id="page-title">Dashboard</h1>
                <p class="text-xs text-gray-500 mt-0.5" id="page-sub">Vue d'ensemble de l'application</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs text-green-400">
                    <span class="w-2 h-2 bg-green-400 rounded-full pulse-dot"></span>
                    Système actif
                </div>
                <button onclick="refreshData()" class="btn-primary px-4 py-2 text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
            </div>
        </div>

        {{-- CONTENT AREA --}}
        <div class="p-8">

            {{-- ══════════════ DASHBOARD TAB ══════════════ --}}
            <div id="tab-dashboard">

                {{-- Stats --}}
                <div class="grid grid-cols-4 gap-5 mb-8">
                    <div class="stat-card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 bg-blue-600/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-ticket text-blue-400 text-sm"></i>
                            </div>
                            <span class="badge badge-blue">Total</span>
                        </div>
                        <p class="text-3xl font-bold text-white" id="stat-reservations">--</p>
                        <p class="text-xs text-gray-500 mt-1">Réservations</p>
                    </div>
                    <div class="stat-card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 bg-green-600/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-money-bill text-green-400 text-sm"></i>
                            </div>
                            <span class="badge badge-green">MAD</span>
                        </div>
                        <p class="text-3xl font-bold text-white" id="stat-revenus">--</p>
                        <p class="text-xs text-gray-500 mt-1">Revenus totaux</p>
                    </div>
                    <div class="stat-card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 bg-yellow-600/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-taxi text-yellow-400 text-sm"></i>
                            </div>
                            <span class="badge badge-yellow">Actifs</span>
                        </div>
                        <p class="text-3xl font-bold text-white" id="stat-taxis">--</p>
                        <p class="text-xs text-gray-500 mt-1">Taxis</p>
                    </div>
                    <div class="stat-card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 bg-purple-600/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-users text-purple-400 text-sm"></i>
                            </div>
                            <span class="badge" style="background:#1e0a3d;color:#c084fc;">Total</span>
                        </div>
                        <p class="text-3xl font-bold text-white" id="stat-users">--</p>
                        <p class="text-xs text-gray-500 mt-1">Utilisateurs</p>
                    </div>
                </div>

                {{-- Dernières réservations --}}
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-bold text-white">Dernières réservations</h2>
                        <button onclick="showTab('reservations')" class="text-xs text-blue-400 hover:underline">Voir tout</button>
                    </div>
                    <div id="recent-reservations">
                        <div class="skeleton h-10 mb-2"></div>
                        <div class="skeleton h-10 mb-2"></div>
                        <div class="skeleton h-10"></div>
                    </div>
                </div>
            </div>

            {{-- ══════════════ RESERVATIONS TAB ══════════════ --}}
            <div id="tab-reservations" class="hidden">
                <div class="card">
                    <div class="p-5 border-b border-gray-800 flex items-center justify-between">
                        <h2 class="font-bold text-white">Toutes les réservations</h2>
                        <span class="badge badge-blue" id="count-reservations">0</span>
                    </div>
                    <div id="table-reservations" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-800">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trajet</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sièges</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Prix</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-reservations"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════ TRAJETS TAB ══════════════ --}}
            <div id="tab-trajets" class="hidden">
                <div class="flex justify-between items-center mb-5">
                    <div></div>
                    <button onclick="openModal('modal-trajet')" class="btn-primary px-4 py-2 text-xs font-semibold flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouveau trajet
                    </button>
                </div>
                <div class="card">
                    <div id="table-trajets" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-800">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Départ</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Arrivée</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Prix</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-trajets"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════ TAXIS TAB ══════════════ --}}
            <div id="tab-taxis" class="hidden">
                <div class="flex justify-between items-center mb-5">
                    <div></div>
                    <button onclick="openModal('modal-taxi')" class="btn-primary px-4 py-2 text-xs font-semibold flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouveau taxi
                    </button>
                </div>
                <div class="card">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-800">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Matricule</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Conducteur</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Capacité</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trajet</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-taxis"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════ VILLES TAB ══════════════ --}}
            <div id="tab-villes" class="hidden">
                <div class="flex justify-between items-center mb-5">
                    <div></div>
                    <button onclick="openModal('modal-ville')" class="btn-primary px-4 py-2 text-xs font-semibold flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouvelle ville
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-4" id="grid-villes"></div>
            </div>

            {{-- ══════════════ USERS TAB ══════════════ --}}
            <div id="tab-users" class="hidden">
                <div class="card">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-800">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Utilisateur</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rôle</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Inscrit le</th>
                                    <th class="px-5 py-3 text-left text-slate-500 font-semibold uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-users"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════ PAIEMENTS TAB ══════════════ --}}
            <div id="tab-paiements" class="hidden">
                <div class="card">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-800">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Montant</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Méthode</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Référence</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-paiements"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- ══════════════ MODALS ══════════════ --}}

    {{-- Modal Ville --}}
    <div id="modal-ville" class="modal-overlay fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="card w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-white">Nouvelle ville</h3>
                <button onclick="closeModal('modal-ville')" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Nom de la ville</label>
                    <input type="text" id="ville-nom" placeholder="Ex: Casablanca"
                        class="input-dark w-full px-4 py-2.5 text-sm">
                </div>
                <button onclick="creerVille()" class="btn-primary w-full py-2.5 text-sm font-semibold">
                    Créer la ville
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Trajet --}}
    <div id="modal-trajet" class="modal-overlay fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="card w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-white">Nouveau trajet</h3>
                <button onclick="closeModal('modal-trajet')" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Ville de départ</label>
                    <select id="trajet-depart" class="input-dark w-full px-4 py-2.5 text-sm">
                        <option value="">Choisir...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Ville d'arrivée</label>
                    <select id="trajet-arrivee" class="input-dark w-full px-4 py-2.5 text-sm">
                        <option value="">Choisir...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Prix (MAD)</label>
                    <input type="number" id="trajet-prix" placeholder="150"
                        class="input-dark w-full px-4 py-2.5 text-sm">
                </div>
                <button onclick="creerTrajet()" class="btn-primary w-full py-2.5 text-sm font-semibold">
                    Créer le trajet
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Taxi --}}
    <div id="modal-taxi" class="modal-overlay fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="card w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-white">Nouveau taxi</h3>
                <button onclick="closeModal('modal-taxi')" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Matricule</label>
                    <input type="text" id="taxi-matricule" placeholder="123-A-45"
                        class="input-dark w-full px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Capacité</label>
                    <select id="taxi-capacite" class="input-dark w-full px-4 py-2.5 text-sm">
                        <option value="4">4 places</option>
                        <option value="5">5 places</option>
                        <option value="6" selected>6 places</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Conducteur</label>
                    <select id="taxi-driver" class="input-dark w-full px-4 py-2.5 text-sm">
                        <option value="">Choisir...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Trajet assigné</label>
                    <select id="taxi-trajet" class="input-dark w-full px-4 py-2.5 text-sm">
                        <option value="">Aucun (assigner plus tard)</option>
                    </select>
                </div>
                <button onclick="creerTaxi()" class="btn-primary w-full py-2.5 text-sm font-semibold">
                    Créer le taxi
                </button>
            </div>
        </div>
    </div>

<script src="{{ asset('js/admin/dashboard.js') }}"></script>

</body>
</html>
