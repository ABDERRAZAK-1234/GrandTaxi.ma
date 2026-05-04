<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Conducteur — GrandTaxi.ma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #0f1117; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #0f1117; }
        ::-webkit-scrollbar-thumb { background: #1e2537; border-radius: 4px; }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.3} }
        .pulse { animation: pulse-dot 2s infinite; }
        @keyframes slide-in { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
        .slide-in { animation: slide-in 0.3s ease; }
        .seat { transition: all 0.2s; cursor: default; }
        .seat-free     { fill: #052e16; stroke: #16a34a; stroke-width: 1.5; }
        .seat-taken    { fill: #2d0a0a; stroke: #dc2626; stroke-width: 1.5; }
        .seat-label    { font-size: 11px; font-weight: 700; pointer-events: none; }
        .seat-label-free  { fill: #4ade80; }
        .seat-label-taken { fill: #f87171; }
    </style>
</head>
<body class="text-slate-200 min-h-screen">

    {{-- ═══ TOPBAR ═══ --}}
    <nav class="bg-[#161b27] border-b border-[#1e2537] px-6 py-4 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-taxi text-white text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-white text-sm leading-tight">GrandTaxi.ma</p>
                <p class="text-xs text-slate-500">Espace Conducteur</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            {{-- Status toggle --}}
            {{-- <div class="flex items-center gap-2 bg-[#0f1117] border border-[#1e2537] rounded-xl px-3 py-2">
                <span class="w-2 h-2 rounded-full pulse" id="status-dot" style="background:#16a34a"></span>
                <span class="text-xs font-semibold" id="status-text">En service</span>
                <button onclick="toggleStatus()" class="ml-2 w-10 h-5 rounded-full relative transition-colors duration-200" id="status-toggle" style="background:#16a34a">
                    <span class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-all" id="toggle-thumb"></span>
                </button>
            </div> --}}

            {{-- Notifications --}}
            <button class="relative text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-bell text-lg"></i>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-xs flex items-center justify-center font-bold" id="notif-badge">0</span>
            </button>

            {{-- Driver info --}}
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold text-white" id="driver-avatar">D</div>
                <div class="hidden md:block">
                    <p class="text-xs font-semibold text-white" id="driver-name">Conducteur</p>
                    <p class="text-xs text-slate-500" id="driver-cne"></p>
                </div>
            </div>

            <button onclick="logout()" class="text-slate-500 hover:text-red-400 transition-colors">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- ═══ STATS ═══ --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                <div class="w-10 h-10 bg-blue-500/10 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-taxi text-blue-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-white" id="stat-taxis">--</p>
                <p class="text-xs text-slate-500 mt-1">Mon taxi</p>
            </div>
            <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                <div class="w-10 h-10 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-ticket text-emerald-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-white" id="stat-reservations">--</p>
                <p class="text-xs text-slate-500 mt-1">Réservations actives</p>
            </div>
            <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                <div class="w-10 h-10 bg-amber-500/10 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-chair text-amber-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-white" id="stat-places">--</p>
                <p class="text-xs text-slate-500 mt-1">Places restantes</p>
            </div>
            <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                <div class="w-10 h-10 bg-violet-500/10 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-money-bill text-violet-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-white" id="stat-revenus">--</p>
                <p class="text-xs text-slate-500 mt-1">Revenus</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ═══ MES TAXIS ═══ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Header taxis --}}
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Mon taxi</h2>
                    <button onclick="openModal('modal-add-taxi')"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Ajouter un taxi
                    </button>
                </div>

                {{-- Taxis list --}}
                <div id="taxis-list" class="space-y-4"></div>

                {{-- ═══ TRAJETS ═══ --}}
                <h2 class="text-lg font-bold text-white pt-2">Mes trajets</h2>
                <div id="trajets-list" class="space-y-3"></div>
            </div>

            {{-- ═══ SIDEBAR DROITE ═══ --}}
            <div class="space-y-6">

                {{-- Profil --}}
                <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-lg font-bold text-white" id="profile-avatar">D</div>
                        <div>
                            <p class="font-bold text-white" id="profile-name">--</p>
                            <p class="text-xs text-slate-500" id="profile-email">--</p>
                        </div>
                    </div>
                    <div class="space-y-2 border-t border-[#1e2537] pt-3">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">CNE</span>
                            <span class="text-white font-medium" id="profile-cne">--</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Permis</span>
                            <span class="text-white font-medium" id="profile-permis">--</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Statut</span>
                            <span class="text-emerald-400 font-semibold" id="profile-statut">Actif</span>
                        </div>
                    </div>
                </div>

                {{-- Réservations récentes --}}
                <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                    <h3 class="font-bold text-white text-sm mb-4">Réservations récentes</h3>
                    <div id="recent-reservations" class="space-y-3"></div>
                </div>

                {{-- Plan des sièges --}}
                <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-white text-sm">Plan des sièges</h3>
                        <select id="select-taxi-plan" onchange="afficherPlanSieges(this.value)"
                            class="bg-[#0f1117] border border-[#1e2537] text-white text-xs rounded-lg px-2 py-1 focus:outline-none">
                            <option value="">Choisir taxi...</option>
                        </select>
                    </div>
                    <div id="plan-sieges" class="flex justify-center">
                        <p class="text-slate-600 text-xs text-center py-4">Sélectionnez un taxi</p>
                    </div>
                    <div class="flex gap-4 justify-center mt-3">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-sm bg-[#052e16] border border-green-600"></div>
                            <span class="text-xs text-slate-500">Libre</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-sm bg-[#2d0a0a] border border-red-600"></div>
                            <span class="text-xs text-slate-500">Occupé</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ═══ TOAST CONTAINER ═══ --}}
    <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-2"></div>

    {{-- ═══ MODAL AJOUTER TAXI ═══ --}}
    <div id="modal-add-taxi" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#161b27] border border-[#1e2537] rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-white">Ajouter mon taxi</h3>
                <button onclick="closeModal('modal-add-taxi')" class="text-slate-500 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Matricule</label>
                    <input type="text" id="taxi-matricule" placeholder="123-A-45"
                        class="w-full bg-[#0f1117] border border-[#1e2537] rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Capacité</label>
                    <select id="taxi-capacite"
                        class="w-full bg-[#0f1117] border border-[#1e2537] rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="4">4 places</option>
                        <option value="5">5 places</option>
                        <option value="6" selected>6 places</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Photo du taxi <span class="text-red-400">*</span></label>
                    <input type="file" id="taxi-image" accept="image/jpeg,image/png,image/jpg,image/webp" required
                        class="w-full bg-[#0f1117] border border-[#1e2537] rounded-xl px-4 py-2.5 text-sm text-slate-400 focus:outline-none focus:border-blue-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:text-xs file:font-semibold">
                    <p class="text-xs text-slate-600 mt-1">Formats acceptés : jpeg, png, jpg, webp. Max 2 Mo.</p>
                </div>
                <div id="modal-error" class="hidden bg-red-900/40 border border-red-800 text-red-300 rounded-xl p-3 text-xs"></div>
                <button onclick="ajouterTaxi()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                    Ajouter le taxi
                </button>
            </div>
        </div>
    </div>

{{-- Inject server-side config for Reverb/Pusher into JS --}}
<script>
    window.REVERB_CONFIG = {
        key:  "{{ env('REVERB_APP_KEY', '') }}",
        host: "{{ env('REVERB_HOST', 'localhost') }}",
        port: {{ env('REVERB_PORT', 8080) }},
    };
</script>
<script src="{{ asset('js/driver.js') }}"></script>

</body>
</html>
