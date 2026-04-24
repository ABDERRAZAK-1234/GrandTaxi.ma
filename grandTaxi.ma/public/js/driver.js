const BASE_URL = '/api';
localStorage.setItem('api_token', response.data.access_token);
const user = JSON.parse(localStorage.getItem('user') || 'null');
const headers = { Authorization: `Bearer ${token}` };

if (!token || !user || user.role !== 'driver') {
    window.location.href = '/login';
}

let myTaxis = [];
let myReservations = [];
let driverStatus = true;

// ─── INIT ───────────────────────────────────────────────
if (user) {
    const initials = user.prenom[0].toUpperCase();
    document.getElementById('driver-avatar').innerText = initials;
    document.getElementById('driver-name').innerText = user.prenom + ' ' + user.nom;
    document.getElementById('driver-cne').innerText = user.cne || '';
    document.getElementById('profile-avatar').innerText = initials;
    document.getElementById('profile-name').innerText = user.prenom + ' ' + user.nom;
    document.getElementById('profile-email').innerText = user.email;
    document.getElementById('profile-cne').innerText = user.cne || '—';
    document.getElementById('profile-permis').innerText = user.permis || '—';
}

// ─── REVERB ─────────────────────────────────────────────
Pusher.logToConsole = false;
const pusher = new Pusher('{{ env("REVERB_APP_KEY") }}', {
    wsHost: '{{ env("REVERB_HOST", "localhost") }}',
    wsPort: {{ env("REVERB_PORT", 8080) }},
forceTLS: false,
    enableStats: false,
        enabledTransports: ['ws'],
            cluster: 'mt1',
    });

pusher.connection.bind('connected', () => {
    console.log('✅ Reverb connecté — Dashboard Conducteur');
});

function ecouterTaxi(trajetId, taxiId) {
    const channel = pusher.subscribe('trajets.' + trajetId);
    channel.bind('reservation.created', function (data) {
        if (data.taxi_id != taxiId) return;
        afficherToast('🎉 Nouveau siège réservé dans le taxi ' + taxiId + ' !', 'emerald');
        loadAll();
        afficherPlanSieges(taxiId);
        document.getElementById('notif-badge').innerText =
            parseInt(document.getElementById('notif-badge').innerText || 0) + 1;
    });
}

// ─── LOAD ALL ───────────────────────────────────────────
async function loadAll() {
    await Promise.all([loadTaxis(), loadStats()]);
}

async function loadTaxis() {
    try {
        const res = await axios.get(`${BASE_URL}/taxis`, { headers });
        myTaxis = res.data.filter(t => t.driver_id == user.id);

        // Populate taxi select for plan
        const select = document.getElementById('select-taxi-plan');
        const currentVal = select.value;
        select.innerHTML = '<option value="">Choisir taxi...</option>' +
            myTaxis.map(t => `<option value="${t.id}">${t.matricule}</option>`).join('');
        if (currentVal) select.value = currentVal;

        document.getElementById('stat-taxis').innerText = myTaxis.length;

        afficherTaxis();

        // S'abonner aux canaux Reverb de chaque taxi
        myTaxis.forEach(taxi => {
            if (taxi.trajet_id) ecouterTaxi(taxi.trajet_id, taxi.id);
        });

    } catch (e) { console.error(e); }
}

async function loadStats() {
    try {
        // Charger les réservations de tous mes taxis
        let totalReservations = 0;
        let totalPlacesRestantes = 0;
        let totalRevenus = 0;
        const allRes = [];

        for (const taxi of myTaxis) {
            const res = await axios.get(`${BASE_URL}/taxis/${taxi.id}/sieges-occupes`, { headers });
            const siegesOccupes = res.data.sieges_occupes?.length || 0;
            totalPlacesRestantes += Math.max(0, taxi.capacite - siegesOccupes);
        }

        // Réservations via admin
        const resAdmin = await axios.get(`${BASE_URL}/admin/reservations`, { headers });
        myReservations = resAdmin.data.filter(r =>
            myTaxis.some(t => t.id == r.taxi_id)
        );

        totalReservations = myReservations.filter(r => r.statut === 'confirmed').length;
        totalRevenus = myReservations
            .filter(r => r.statut === 'confirmed')
            .reduce((s, r) => s + parseFloat(r.prix_total || 0), 0);

        document.getElementById('stat-reservations').innerText = totalReservations;
        document.getElementById('stat-places').innerText = totalPlacesRestantes;
        document.getElementById('stat-revenus').innerText = totalRevenus.toFixed(0) + ' MAD';

        afficherReservationsRecentes();

    } catch (e) { console.error(e); }
}

// ─── AFFICHER TAXIS ─────────────────────────────────────
function afficherTaxis() {
    const container = document.getElementById('taxis-list');

    if (myTaxis.length === 0) {
        container.innerHTML = `
                <div class="bg-[#161b27] border border-[#1e2537] border-dashed rounded-2xl p-8 text-center">
                    <i class="fas fa-taxi text-slate-600 text-3xl mb-3"></i>
                    <p class="text-slate-500 text-sm">Aucun taxi enregistré</p>
                    <button onclick="openModal('modal-add-taxi')"
                        class="mt-3 text-blue-400 text-xs hover:underline">
                        Ajouter mon premier taxi
                    </button>
                </div>
            `;
        return;
    }

    container.innerHTML = myTaxis.map(taxi => {
        const statusColors = {
            available: 'bg-emerald-900/50 text-emerald-300',
            reserved: 'bg-blue-900/50 text-blue-300',
            full: 'bg-red-900/50 text-red-300',
            unavailable: 'bg-slate-700 text-slate-400',
        };

        const trajetInfo = taxi.trajet
            ? `Trajet #${taxi.trajet.id} — ${taxi.trajet.prix} MAD`
            : 'Aucun trajet assigné';

        return `
                <div class="bg-[#161b27] border border-[#1e2537] hover:border-blue-500/40 rounded-2xl p-5 transition-colors">
                    <div class="flex items-start gap-4">

                        {{-- Image taxi --}}
                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 bg-[#0f1117] border border-[#1e2537]">
                            ${taxi.image_url
                ? `<img src="${taxi.image_url}" class="w-full h-full object-cover" alt="${taxi.matricule}">`
                : `<div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-taxi text-slate-600 text-2xl"></i>
                                   </div>`
            }
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-bold text-white">${taxi.matricule}</p>
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full ${statusColors[taxi.statuts] || 'bg-slate-700 text-slate-400'}">
                                    ${taxi.statuts}
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs text-slate-400">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-chair w-4 text-center text-blue-400"></i>
                                    ${taxi.capacite} places
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-route w-4 text-center text-blue-400"></i>
                                    ${trajetInfo}
                                </div>
                            </div>

                            {{-- Places visuelles --}}
                            <div class="flex gap-1 mt-3" id="places-taxi-${taxi.id}">
                                ${Array.from({ length: taxi.capacite }, (_, i) =>
                `<div class="w-5 h-5 rounded-sm bg-emerald-900/50 border border-emerald-700" title="S${i + 1}"></div>`
            ).join('')}
                            </div>
                        </div>

                        <button onclick="afficherPlanSieges(${taxi.id})"
                            class="text-slate-500 hover:text-blue-400 transition-colors flex-shrink-0" title="Voir plan">
                            <i class="fas fa-map-marked-alt"></i>
                        </button>
                    </div>
                </div>
            `;
    }).join('');

    // Charger les sièges occupés pour chaque taxi
    myTaxis.forEach(taxi => chargerPlacesTaxi(taxi));

    // Charger les trajets
    afficherTrajets();
}

async function chargerPlacesTaxi(taxi) {
    try {
        const res = await axios.get(`${BASE_URL}/taxis/${taxi.id}/sieges-occupes`, { headers });
        const occupes = res.data.sieges_occupes || [];
        const container = document.getElementById(`places-taxi-${taxi.id}`);
        if (!container) return;

        container.innerHTML = Array.from({ length: taxi.capacite }, (_, i) => {
            const num = i + 1;
            const pris = occupes.includes(num) || occupes.includes(String(num));
            return `<div class="w-5 h-5 rounded-sm ${pris ? 'bg-red-900/50 border border-red-700' : 'bg-emerald-900/50 border border-emerald-700'}"
                    title="S${num} — ${pris ? 'Occupé' : 'Libre'}"></div>`;
        }).join('');
    } catch (e) { }
}

// ─── AFFICHER TRAJETS ───────────────────────────────────
async function afficherTrajets() {
    const container = document.getElementById('trajets-list');
    const trajetIds = [...new Set(myTaxis.filter(t => t.trajet_id).map(t => t.trajet_id))];

    if (trajetIds.length === 0) {
        container.innerHTML = `<p class="text-slate-600 text-sm text-center py-4">Aucun trajet assigné</p>`;
        return;
    }

    try {
        const res = await axios.get(`${BASE_URL}/trajets`, { headers });
        const trajets = res.data.filter(t => trajetIds.includes(t.id));

        container.innerHTML = trajets.map(t => {
            const statusColors = {
                actif: 'bg-emerald-900/50 text-emerald-300',
                cloture: 'bg-red-900/50 text-red-300',
            };
            return `
                    <div class="bg-[#161b27] border border-[#1e2537] rounded-xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-route text-blue-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-white text-sm font-semibold">Trajet #${t.id}</p>
                                <p class="text-slate-500 text-xs">${t.prix} MAD / place</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full ${statusColors[t.statut] || 'bg-slate-700 text-slate-400'}">
                            ${t.statut}
                        </span>
                    </div>
                `;
        }).join('');
    } catch (e) { console.error(e); }
}

// ─── PLAN SIEGES ────────────────────────────────────────
async function afficherPlanSieges(taxiId) {
    if (!taxiId) return;

    const select = document.getElementById('select-taxi-plan');
    select.value = taxiId;

    try {
        const res = await axios.get(`${BASE_URL}/taxis/${taxiId}/sieges-occupes`, { headers });
        const occupes = res.data.sieges_occupes || [];
        const taxi = myTaxis.find(t => t.id == taxiId);
        if (!taxi) return;

        const capacite = taxi.capacite || 6;

        // SVG du plan
        const svgContent = `
                <svg viewBox="0 0 140 320" width="140" height="320" xmlns="http://www.w3.org/2000/svg">
                    <rect x="5" y="5" width="130" height="310" rx="30" fill="#1e2537" stroke="#374151" stroke-width="1.5"/>
                    <rect x="20" y="20" width="100" height="280" rx="15" fill="#0f1117"/>
                    <circle cx="45" cy="65" r="16" fill="#1e2537" stroke="#374151" stroke-width="1"/>
                    ${getSiegeSVG(1, 78, 48, occupes)}
                    ${getSiegeSVG(2, 28, 120, occupes)}
                    ${getSiegeSVG(3, 78, 120, occupes)}
                    ${capacite >= 5 ? getSiegeSVG(4, 18, 190, occupes) : ''}
                    ${capacite >= 5 ? getSiegeSVG(5, 68, 190, occupes) : ''}
                    ${capacite >= 6 ? getSiegeSVG(6, 43, 255, occupes) : ''}
                </svg>
            `;

        document.getElementById('plan-sieges').innerHTML = svgContent;
    } catch (e) { console.error(e); }
}

function getSiegeSVG(num, x, y, occupes) {
    const pris = occupes.includes(num) || occupes.includes(String(num));
    const cls = pris ? 'seat-taken' : 'seat-free';
    const lcls = pris ? 'seat-label-taken' : 'seat-label-free';
    return `
            <rect x="${x}" y="${y}" width="40" height="48" rx="8" class="seat ${cls}"/>
            <text x="${x + 20}" y="${y + 28}" text-anchor="middle" class="seat-label ${lcls}">S${num}</text>
        `;
}

// ─── RESERVATIONS RECENTES ──────────────────────────────
function afficherReservationsRecentes() {
    const container = document.getElementById('recent-reservations');
    const recent = myReservations.slice(-5).reverse();

    if (recent.length === 0) {
        container.innerHTML = `<p class="text-slate-600 text-xs text-center py-2">Aucune réservation</p>`;
        return;
    }

    container.innerHTML = recent.map(r => `
            <div class="flex items-center gap-3 py-2 border-b border-[#1e2537] last:border-0">
                <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                    ${r.user?.prenom?.[0] || '?'}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">${r.user?.prenom || ''} ${r.user?.nom || ''}</p>
                    <p class="text-slate-500 text-xs">${r.sieges ? (Array.isArray(r.sieges) ? r.sieges : JSON.parse(r.sieges)).map(s => 'S' + s).join(', ') : r.nombre_place + ' place(s)'}</p>
                </div>
                <span class="text-xs font-bold text-blue-400">${r.prix_total} MAD</span>
            </div>
        `).join('');
}

// ─── AJOUTER TAXI ───────────────────────────────────────
async function ajouterTaxi() {
    const formData = new FormData();
    formData.append('matricule', document.getElementById('taxi-matricule').value);
    formData.append('capacite', document.getElementById('taxi-capacite').value);
    formData.append('statuts', document.getElementById('taxi-statut').value);

    const imageFile = document.getElementById('taxi-image').files[0];
    if (imageFile) formData.append('image', imageFile);

    const errEl = document.getElementById('modal-error');
    errEl.classList.add('hidden');

    try {
        await axios.post(`${BASE_URL}/admin/taxis`, formData, {
            headers: {
                ...headers,
                'Content-Type': 'multipart/form-data',
            }
        });
        closeModal('modal-add-taxi');
        afficherToast('✅ Taxi ajouté avec succès !', 'emerald');
        await loadTaxis();
    } catch (e) {
        const msg = e.response?.data?.message ||
            Object.values(e.response?.data?.errors || {}).flat().join(' | ') ||
            'Erreur';
        errEl.innerText = msg;
        errEl.classList.remove('hidden');
    }
}

// ─── STATUS TOGGLE ──────────────────────────────────────
async function toggleStatus() {
    driverStatus = !driverStatus;
    const dot = document.getElementById('status-dot');
    const text = document.getElementById('status-text');
    const toggle = document.getElementById('status-toggle');
    const thumb = document.getElementById('toggle-thumb');

    if (driverStatus) {
        dot.style.background = '#16a34a';
        text.innerText = 'En service';
        toggle.style.background = '#16a34a';
        thumb.style.right = '2px';
        thumb.style.left = 'auto';
    } else {
        dot.style.background = '#dc2626';
        text.innerText = 'Hors service';
        toggle.style.background = '#dc2626';
        thumb.style.left = '2px';
        thumb.style.right = 'auto';
    }
}

// ─── TOAST ──────────────────────────────────────────────
function afficherToast(msg, color = 'blue') {
    const colors = {
        blue: 'bg-blue-600',
        emerald: 'bg-emerald-600',
        red: 'bg-red-600',
        amber: 'bg-amber-600',
    };
    const toast = document.createElement('div');
    toast.className = `slide-in ${colors[color] || colors.blue} text-white px-4 py-3 rounded-xl shadow-lg text-xs font-semibold flex items-center gap-2 max-w-xs`;
    toast.innerHTML = msg;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ─── MODAL ──────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// ─── LOGOUT ─────────────────────────────────────────────
async function logout() {
    try { await axios.post(`${BASE_URL}/logout`, {}, { headers }); } catch (e) { }
    localStorage.removeItem('api_token');
    localStorage.removeItem('user');
    window.location.href = '/login';
}

// ─── AUTO REFRESH ───────────────────────────────────────
loadAll();
setInterval(loadAll, 30000);
