const BASE_URL = '/api';
const token = localStorage.getItem('api_token');
const user = JSON.parse(localStorage.getItem('user') || 'null');
const headers = { Authorization: `Bearer ${token}` };

if (!token || !user || user.role !== 'driver') {
    window.location.href = '/login';
}

// Pending guard — driver registered but not yet approved by admin
if (user && user.status === 'pending') {
    document.body.innerHTML = `
        <div style="
            min-height:100vh; background:#0a0f1a; display:flex;
            align-items:center; justify-content:center; font-family:'Inter',sans-serif;
        ">
            <div style="
                background:#111827; border:1px solid #1e2537; border-radius:1.5rem;
                padding:3rem 2.5rem; max-width:420px; text-align:center;
            ">
                <div style="font-size:3.5rem; margin-bottom:1.25rem;">⏳</div>
                <h1 style="color:#f8fafc; font-size:1.25rem; font-weight:700; margin-bottom:.75rem;">
                    Compte en attente d'approbation
                </h1>
                <p style="color:#94a3b8; font-size:.875rem; line-height:1.6; margin-bottom:2rem;">
                    Votre demande d'inscription en tant que conducteur a bien été reçue.
                    Un administrateur va vérifier vos informations et activer votre compte sous peu.
                </p>
                <p style="color:#64748b; font-size:.75rem; margin-bottom:2rem;">
                    Une fois approuvé, vous pourrez vous connecter et accéder à votre tableau de bord.
                </p>
                <button onclick="logoutPending()" style="
                    background:#1e40af; color:#fff; border:none; border-radius:.75rem;
                    padding:.75rem 2rem; font-size:.875rem; font-weight:600; cursor:pointer;
                ">Se déconnecter</button>
            </div>
        </div>
    `;
    window.logoutPending = function() {
        localStorage.removeItem('api_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    };
    // Stop executing the rest of driver.js
    throw new Error('DRIVER_PENDING');
}

// Dashboard state
let dashboardData = null;
let driverStatus = true;

// ─── INIT
if (user) {
    var initials = (user.prenom && user.prenom[0]) ? user.prenom[0].toUpperCase() : 'D';
    document.getElementById('driver-avatar').innerText = initials;
    document.getElementById('driver-name').innerText = (user.prenom || '') + ' ' + (user.nom || '');
    document.getElementById('profile-avatar').innerText = initials;
    document.getElementById('profile-name').innerText = (user.prenom || '') + ' ' + (user.nom || '');
    document.getElementById('profile-email').innerText = user.email || '';

    // Pre-populate driver profile from localStorage (if available from login/register)
    var dp = user.driver_profile;
    if (dp) {
        document.getElementById('driver-cne').innerText = dp.cne || '';
        document.getElementById('profile-cne').innerText = dp.cne || '—';
        document.getElementById('profile-permis').innerText = dp.permis || '—';
    }
}

// ─── REVERB
// Reverb config is injected from Blade
function initReverb() {
    if (typeof Pusher === 'undefined' || !window.REVERB_CONFIG) return;

    Pusher.logToConsole = false;
    const cfg = window.REVERB_CONFIG;
    const pusher = new Pusher(cfg.key, {
        wsHost: cfg.host,
        wsPort: cfg.port,
        forceTLS: false,
        enableStats: false,
        enabledTransports: ['ws'],
        cluster: 'mt1',
    });

    pusher.connection.bind('connected', () => {
        console.log('Reverb connecté — Dashboard Conducteur');
    });

    window._pusher = pusher;
}

function ecouterTaxi(trajetId, taxiId) {
    if (!window._pusher) return;
    const channel = window._pusher.subscribe('trajets.' + trajetId);

    // Prevent duplicate event listeners when dashboard reloads
    channel.unbind('reservation.created');

    channel.bind('reservation.created', function (data) {
        if (data.taxi_id != taxiId) return;
        afficherToast('🎉 Nouveau siège réservé !', 'emerald');
        loadDashboard();
        afficherPlanSieges(taxiId);
        const badge = document.getElementById('notif-badge');
        badge.innerText = parseInt(badge.innerText || 0) + 1;
    });
}

// ─── LOAD DASHBOARD
async function loadDashboard() {
    try {
        const res = await axios.get(`${BASE_URL}/driver/dashboard`, { headers });
        dashboardData = res.data;

        // Update profile section from API response
        const driver = dashboardData.driver;
        const profile = dashboardData.profile;

        if (driver) {
            const initials = driver.prenom?.[0]?.toUpperCase() || 'D';
            document.getElementById('driver-avatar').innerText = initials;
            document.getElementById('driver-name').innerText = (driver.prenom || '') + ' ' + (driver.nom || '');
            document.getElementById('profile-avatar').innerText = initials;
            document.getElementById('profile-name').innerText = (driver.prenom || '') + ' ' + (driver.nom || '');
            document.getElementById('profile-email').innerText = driver.email || '';
        }

        // Update driver profile
        if (profile) {
            document.getElementById('driver-cne').innerText = profile.cne || '';
            document.getElementById('profile-cne').innerText = profile.cne || '—';
            document.getElementById('profile-permis').innerText = profile.permis || '—';
        } else {
            document.getElementById('driver-cne').innerText = '';
            document.getElementById('profile-cne').innerText = '—';
            document.getElementById('profile-permis').innerText = '—';
        }

        // Update stats
        const stats = dashboardData.stats || {};
        document.getElementById('stat-taxis').innerText = dashboardData.taxi ? '1' : '0';
        document.getElementById('stat-reservations').innerText = stats.confirmed || 0;
        document.getElementById('stat-places').innerText = stats.places_restantes || 0;
        document.getElementById('stat-revenus').innerText = (stats.revenus || 0).toFixed(0) + ' MAD';

        // Render taxi card
        afficherTaxi();

        // Render recent reservations
        afficherReservationsRecentes();

        // Subscribe to Reverb for real-time updates
        if (dashboardData.taxi && dashboardData.taxi.trajet_id) {
            ecouterTaxi(dashboardData.taxi.trajet_id, dashboardData.taxi.id);
        }

        // Populate seat plan selector
        const select = document.getElementById('select-taxi-plan');
        if (dashboardData.taxi) {
            select.innerHTML = `<option value="${dashboardData.taxi.id}">${dashboardData.taxi.matricule}</option>`;
        } else {
            select.innerHTML = '<option value="">Aucun taxi</option>';
        }

    } catch (e) {
        console.error('Erreur chargement dashboard:', e);
        // If 401 Unauthorized → redirect to login
        if (e.response && e.response.status === 401) {
            localStorage.removeItem('api_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
        // If 403 Forbidden → role mismatch
        if (e.response && e.response.status === 403) {
            console.error('Accès interdit — rôle incorrect.');
        }
    }
}

// ─── AFFICHER TAXI
function afficherTaxi() {
    const container = document.getElementById('taxis-list');
    const taxi = dashboardData ? dashboardData.taxi : null;

    if (!taxi) {
        container.innerHTML = `
            <div class="bg-[#161b27] border border-[#1e2537] border-dashed rounded-2xl p-8 text-center">
                <i class="fas fa-taxi text-slate-600 text-3xl mb-3"></i>
                <p class="text-slate-500 text-sm">Aucun taxi enregistré</p>
                <button onclick="openModal('modal-add-taxi')"
                    class="mt-3 text-blue-400 text-xs hover:underline">
                    Ajouter mon taxi
                </button>
            </div>
        `;
        return;
    }

    // If driver already has a taxi, hide the header "add" button
    document.querySelectorAll('button').forEach(function (btn) {
        if (btn.textContent.trim().includes('Ajouter un taxi') &&
            btn.closest('.flex.items-center.justify-between')) {
            btn.style.display = 'none';
        }
    });

    var statusColors = {
        available: 'bg-emerald-900/50 text-emerald-300',
        reserved: 'bg-blue-900/50 text-blue-300',
        full: 'bg-red-900/50 text-red-300',
        unavailable: 'bg-slate-700 text-slate-400',
    };

    var trajetInfo = taxi.trajet
        ? 'Trajet #' + taxi.trajet.id + ' — ' + taxi.trajet.prix + ' MAD'
        : 'Aucun trajet assigné';

    var imageHtml = taxi.image_url
        ? '<img src="' + taxi.image_url + '" class="w-full h-full object-cover" alt="' + taxi.matricule + '">'
        : '<div class="w-full h-full flex items-center justify-center"><i class="fas fa-taxi text-slate-600 text-2xl"></i></div>';

    var placesHtml = '';
    for (var i = 0; i < taxi.capacite; i++) {
        placesHtml += '<div class="w-5 h-5 rounded-sm bg-emerald-900/50 border border-emerald-700" title="S' + (i + 1) + '"></div>';
    }

    container.innerHTML =
        '<div class="bg-[#161b27] border border-[#1e2537] hover:border-blue-500/40 rounded-2xl p-5 transition-colors">' +
        '<div class="flex items-start gap-4">' +
        '<div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 bg-[#0f1117] border border-[#1e2537]">' +
        imageHtml +
        '</div>' +
        '<div class="flex-1 min-w-0">' +
        '<div class="flex items-center justify-between mb-2">' +
        '<p class="font-bold text-white">' + taxi.matricule + '</p>' +
        '<span class="text-xs font-semibold px-2.5 py-0.5 rounded-full ' + (statusColors[taxi.statuts] || 'bg-slate-700 text-slate-400') + '">' +
        taxi.statuts +
        '</span>' +
        '</div>' +
        '<div class="space-y-1.5 text-xs text-slate-400">' +
        '<div class="flex items-center gap-2">' +
        '<i class="fas fa-chair w-4 text-center text-blue-400"></i>' +
        taxi.capacite + ' places' +
        '</div>' +
        '<div class="flex items-center gap-2">' +
        '<i class="fas fa-route w-4 text-center text-blue-400"></i>' +
        trajetInfo +
        '</div>' +
        '</div>' +
        '<div class="flex gap-1 mt-3" id="places-taxi-' + taxi.id + '">' +
        placesHtml +
        '</div>' +
        '</div>' +
        '<button onclick="afficherPlanSieges(' + taxi.id + ')"' +
        ' class="text-slate-500 hover:text-blue-400 transition-colors flex-shrink-0" title="Voir plan">' +
        '<i class="fas fa-map-marked-alt"></i>' +
        '</button>' +
        (taxi.trajet ? '<button onclick="reverseTrip()"' +
        ' class="text-slate-500 hover:text-amber-400 transition-colors flex-shrink-0 ml-2" title="Trajet Retour">' +
        '<i class="fas fa-exchange-alt"></i>' +
        '</button>' : '') +
        '</div>' +
        '</div>';

    // Load occupied seats visualization
    chargerPlacesTaxi(taxi);

    // Show trajets section
    afficherTrajets();
}

async function chargerPlacesTaxi(taxi) {
    try {
        var res = await axios.get(BASE_URL + '/taxis/' + taxi.id + '/sieges-occupes', { headers: headers });
        var occupes = res.data.sieges_occupes || [];
        var container = document.getElementById('places-taxi-' + taxi.id);
        if (!container) return;

        var html = '';
        for (var i = 0; i < taxi.capacite; i++) {
            var num = i + 1;
            var pris = occupes.indexOf(num) !== -1 || occupes.indexOf(String(num)) !== -1;
            html += '<div class="w-5 h-5 rounded-sm ' +
                (pris ? 'bg-red-900/50 border border-red-700' : 'bg-emerald-900/50 border border-emerald-700') +
                '" title="S' + num + ' — ' + (pris ? 'Occupé' : 'Libre') + '"></div>';
        }
        container.innerHTML = html;
    } catch (e) { /* silent */ }
}

// ─── AFFICHER TRAJETS
async function afficherTrajets() {
    var container = document.getElementById('trajets-list');
    var taxi = dashboardData ? dashboardData.taxi : null;

    if (!taxi || !taxi.trajet_id) {
        container.innerHTML = '<p class="text-slate-600 text-sm text-center py-4">Aucun trajet assigné</p>';
        return;
    }

    try {
        var res = await axios.get(BASE_URL + '/trajets/' + taxi.trajet_id, { headers: headers });
        var t = res.data;

        var statusColors = {
            actif: 'bg-emerald-900/50 text-emerald-300',
            cloture: 'bg-red-900/50 text-red-300',
        };

        container.innerHTML =
            '<div class="bg-[#161b27] border border-[#1e2537] rounded-xl p-4 flex items-center justify-between">' +
            '<div class="flex items-center gap-3">' +
            '<div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">' +
            '<i class="fas fa-route text-blue-400 text-xs"></i>' +
            '</div>' +
            '<div>' +
            '<p class="text-white text-sm font-semibold">Trajet #' + t.id + '</p>' +
            '<p class="text-slate-500 text-xs">' + t.prix + ' MAD / place</p>' +
            '</div>' +
            '</div>' +
            '<span class="text-xs font-semibold px-2.5 py-0.5 rounded-full ' + (statusColors[t.statut] || 'bg-slate-700 text-slate-400') + '">' +
            t.statut +
            '</span>' +
            '</div>';
    } catch (e) { console.error(e); }
}

// ─── PLAN SIEGES
async function afficherPlanSieges(taxiId) {
    if (!taxiId) return;

    var select = document.getElementById('select-taxi-plan');
    select.value = taxiId;

    try {
        var res = await axios.get(BASE_URL + '/taxis/' + taxiId + '/sieges-occupes', { headers: headers });
        var occupes = res.data.sieges_occupes || [];
        var taxi = dashboardData ? dashboardData.taxi : null;
        if (!taxi || taxi.id != taxiId) return;

        var capacite = taxi.capacite || 6;

        var svgContent =
            '<div class="relative w-full max-w-[240px] mx-auto aspect-[220/480] mt-4">' +
            '<img src="/images/taxi_kbir_booking_seats-Photoroom12.png" class="absolute inset-0 w-full h-full object-contain rounded-xl" alt="Plan du taxi">' +
            '<svg viewBox="0 0 220 480" preserveAspectRatio="xMidYMid meet" class="absolute inset-0 w-full h-full">' +
            getSiegeSVG(1, 133, 185, occupes) +
            getSiegeSVG(2, 63, 260, occupes) +
            getSiegeSVG(3, 99, 260, occupes) +
            getSiegeSVG(4, 135, 260, occupes) +
            (capacite >= 5 ? getSiegeSVG(5, 73, 340, occupes) : '') +
            (capacite >= 6 ? getSiegeSVG(6, 124, 340, occupes) : '') +
            '</svg>' +
            '</div>';

        document.getElementById('plan-sieges').innerHTML = svgContent;
    } catch (e) { console.error(e); }
}

function getSiegeSVG(num, x, y, occupes) {
    var pris = occupes.indexOf(num) !== -1 || occupes.indexOf(String(num)) !== -1;
    var cls = pris ? 'seat-taken' : 'seat-free';
    var lcls = pris ? 'seat-label-taken' : 'seat-label-free';
    return '<rect x="' + x + '" y="' + y + '" width="25" height="30" rx="10" class="seat ' + cls + '"/>' +
        '<text x="' + (x + 12.5) + '" y="' + (y + 20) + '" text-anchor="middle" class="seat-label ' + lcls + '">S' + num + '</text>';
}

// ─── RESERVATIONS RECENTES
function afficherReservationsRecentes() {
    var container = document.getElementById('recent-reservations');
    var reservations = dashboardData ? (dashboardData.reservations || []) : [];
    var recent = reservations.slice(0, 5);

    if (recent.length === 0) {
        container.innerHTML = '<p class="text-slate-600 text-xs text-center py-2">Aucune réservation</p>';
        return;
    }

    var html = '';
    for (var i = 0; i < recent.length; i++) {
        var r = recent[i];
        var initial = (r.user && r.user.prenom) ? r.user.prenom[0] : '?';
        var fullName = ((r.user && r.user.prenom) || '') + ' ' + ((r.user && r.user.nom) || '');

        var siegesText = '';
        if (r.sieges) {
            var siegesArr = Array.isArray(r.sieges) ? r.sieges : JSON.parse(r.sieges);
            siegesText = siegesArr.map(function (s) { return 'S' + s; }).join(', ');
        } else {
            siegesText = r.nombre_place + ' place(s)';
        }

        html +=
            '<div class="flex items-center gap-3 py-2 border-b border-[#1e2537] last:border-0">' +
            '<div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0">' +
            initial +
            '</div>' +
            '<div class="flex-1 min-w-0">' +
            '<p class="text-white text-xs font-medium truncate">' + fullName + '</p>' +
            '<p class="text-slate-500 text-xs">' + siegesText + '</p>' +
            '</div>' +
            '<span class="text-xs font-bold text-blue-400">' + r.prix_total + ' MAD</span>' +
            '</div>';
    }
    container.innerHTML = html;
}

// ─── TRAJET RETOUR
async function reverseTrip() {
    if (!confirm('Confirmez-vous l\'inversion du trajet ? Votre taxi sera placé en fin de file pour le trajet retour.')) return;

    try {
        const res = await axios.post(BASE_URL + '/driver/reverse-trip', {}, { headers });
        afficherToast('✅ ' + res.data.message, 'emerald');

        // Reload dashboard to show new trip
        await loadDashboard();

    } catch (e) {
        const msg = e.response?.data?.message || 'Erreur lors de l\'inversion du trajet.';
        afficherToast('❌ ' + msg, 'red');
    }
}

// ─── AJOUTER TAXI (driver endpoint)
async function ajouterTaxi() {
    var formData = new FormData();
    formData.append('matricule', document.getElementById('taxi-matricule').value);
    formData.append('capacite', document.getElementById('taxi-capacite').value);

    var imageFile = document.getElementById('taxi-image').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }

    var errEl = document.getElementById('modal-error');
    errEl.classList.add('hidden');

    try {
        // Use the driver endpoint — driver_id is set automatically server-side
        await axios.post(BASE_URL + '/driver/taxi', formData, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'multipart/form-data',
            }
        });
        closeModal('modal-add-taxi');
        afficherToast('✅ Taxi ajouté avec succès !', 'emerald');
        await loadDashboard();
    } catch (e) {
        var msg = '';
        if (e.response && e.response.data) {
            if (e.response.data.errors) {
                var allErrors = [];
                var errObj = e.response.data.errors;
                for (var key in errObj) {
                    if (errObj.hasOwnProperty(key)) {
                        allErrors = allErrors.concat(errObj[key]);
                    }
                }
                msg = allErrors.join(' | ');
            } else {
                msg = e.response.data.message || 'Erreur lors de la création du taxi.';
            }
        } else {
            msg = 'Erreur réseau.';
        }
        errEl.innerText = msg;
        errEl.classList.remove('hidden');
    }
}

// ─── STATUS TOGGLE 
// function toggleStatus() {
//     driverStatus = !driverStatus;
//     var dot = document.getElementById('status-dot');
//     var text = document.getElementById('status-text');
//     var toggle = document.getElementById('status-toggle');
//     var thumb = document.getElementById('toggle-thumb');

//     if (driverStatus) {
//         dot.style.background = '#16a34a';
//         text.innerText = 'En service';
//         toggle.style.background = '#16a34a';
//         thumb.style.right = '2px';
//         thumb.style.left = 'auto';
//     } else {
//         dot.style.background = '#dc2626';
//         text.innerText = 'Hors service';
//         toggle.style.background = '#dc2626';
//         thumb.style.left = '2px';
//         thumb.style.right = 'auto';
//     }
// }

// ─── TOAST
function afficherToast(msg, color) {
    color = color || 'blue';
    var colors = {
        blue: 'bg-blue-600',
        emerald: 'bg-emerald-600',
        red: 'bg-red-600',
        amber: 'bg-amber-600',
    };
    var toast = document.createElement('div');
    toast.className = 'slide-in ' + (colors[color] || colors.blue) + ' text-white px-4 py-3 rounded-xl shadow-lg text-xs font-semibold flex items-center gap-2 max-w-xs';
    toast.innerHTML = msg;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(function () {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s';
        setTimeout(function () { toast.remove(); }, 300);
    }, 4000);
}

// ─── MODAL
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// ─── LOGOUT
async function logout() {
    try { await axios.post(BASE_URL + '/logout', {}, { headers: headers }); } catch (e) { }
    localStorage.removeItem('api_token');
    localStorage.removeItem('user');
    window.location.href = '/login';
}

// ─── BOOT
initReverb();
loadDashboard();
setInterval(loadDashboard, 30000);
