const BASE_URL = "/api";
const token = localStorage.getItem("api_token");
const user = JSON.parse(localStorage.getItem("user") || "null");

if (!token || !user || user.role !== "admin") {
    window.location.href = "/login";
}

// Init admin info
if (user) {
    document.getElementById("admin-name").innerText =
        user.prenom + " " + user.nom;
    document.getElementById("admin-avatar").innerText =
        user.prenom[0].toUpperCase();
}

const headers = { Authorization: `Bearer ${token}` };
let villesData = [];
let trajetsData = [];
let usersData = [];

// ─── TABS
const tabs = [
    "dashboard",
    "reservations",
    "trajets",
    "taxis",
    "villes",
    "users",
    "paiements",
];
const titles = {
    dashboard: ["Dashboard", "Vue d'ensemble de l'application"],
    reservations: ["Réservations", "Toutes les réservations clients"],
    trajets: ["Trajets", "Gestion des trajets intercités"],
    taxis: ["Taxis", "Gestion de la flotte de taxis"],
    villes: ["Villes", "Gestion des villes disponibles"],
    users: ["Utilisateurs", "Gestion des comptes"],
    paiements: ["Paiements", "Historique des transactions Stripe"],
};

function showTab(tab) {
    tabs.forEach((t) => {
        document.getElementById("tab-" + t).classList.add("hidden");
        const nav = document.getElementById("nav-" + t);
        if (nav) {
            nav.classList.remove("active");
            nav.querySelector("span")?.classList.remove("text-white");
            nav.querySelector("span")?.classList.add("text-gray-400");
            nav.querySelector("i")?.classList.remove("text-blue-300");
            nav.querySelector("i")?.classList.add("text-gray-400");
        }
    });

    document.getElementById("tab-" + tab).classList.remove("hidden");
    const activeNav = document.getElementById("nav-" + tab);
    if (activeNav) {
        activeNav.classList.add("active");
        activeNav.querySelector("span")?.classList.add("text-white");
        activeNav.querySelector("span")?.classList.remove("text-gray-400");
        activeNav.querySelector("i")?.classList.add("text-blue-300");
        activeNav.querySelector("i")?.classList.remove("text-gray-400");
    }

    document.getElementById("page-title").innerText = titles[tab][0];
    document.getElementById("page-sub").innerText = titles[tab][1];

    loadTab(tab);
}

async function loadTab(tab) {
    if (tab === "dashboard") await loadDashboard();
    if (tab === "reservations") await loadReservations();
    if (tab === "trajets") await loadTrajets();
    if (tab === "taxis") await loadTaxis();
    if (tab === "villes") await loadVilles();
    if (tab === "users") { await loadPendingDrivers(); await loadUsers(); }
    if (tab === "paiements") await loadPaiements();
}

// ─── DASHBOARD--
async function loadDashboard() {
    try {
        const [resResa, resTaxis, resUsers, resPaiements, resVilles] = await Promise.all([
            axios.get(`${BASE_URL}/admin/reservations`, { headers }),
            axios.get(`${BASE_URL}/taxis`, { headers }),
            axios.get(`${BASE_URL}/admin/users`, { headers }),
            axios.get(`${BASE_URL}/admin/paiements`, { headers }),
            axios.get(`${BASE_URL}/villes`, { headers }),
        ]);

        const reservations = resResa.data;
        const taxis = resTaxis.data;
        const users = resUsers.data;
        const paiements = resPaiements.data;

        document.getElementById("stat-reservations").innerText =
            reservations.length;
        document.getElementById("stat-taxis").innerText = taxis.length;
        document.getElementById("stat-users").innerText = users.length;

        const totalRevenus = paiements
            .filter((p) => p.statut === "paid")
            .reduce((sum, p) => sum + parseFloat(p.montant), 0);
        document.getElementById("stat-revenus").innerText =
            totalRevenus.toFixed(0) + " MAD";

        document.getElementById("badge-reservations").innerText =
            reservations.length;
        document.getElementById("count-reservations").innerText =
            reservations.length;

        // Recent reservations
        const villes = resVilles.data;
        const villeMap = {};
        villes.forEach(v => villeMap[v.id] = v.nom);

        const recent = reservations.slice(-5).reverse();
        const html = recent.length
            ? recent
                  .map(
                      (r) => `
                <div class="table-row flex items-center px-2 py-3 gap-4">
                    <span class="text-gray-500 text-xs w-12">#${String(r.id).padStart(4, "0")}</span>
                    <span class="text-white text-xs flex-1">${r.user?.prenom || ""} ${r.user?.nom || ""}</span>
                    <span class="text-gray-400 text-xs flex-1">${r.trajet ? (villeMap[r.trajet.ville_depart_id] || "") + " → " + (villeMap[r.trajet.ville_arrivee_id] || "") : ""}</span>
                    <span class="text-blue-400 text-xs font-bold">${r.prix_total} MAD</span>
                    <span class="badge ${r.statut === "confirmed" ? "badge-green" : r.statut === "completed" ? "badge-blue" : "badge-red"}">${r.statut}</span>
                </div>
            `,
                  )
                  .join("")
            : '<p class="text-gray-500 text-sm p-4 text-center">Aucune réservation</p>';

        document.getElementById("recent-reservations").innerHTML = html;
    } catch (e) {
        console.error(e);
    }
}

// ─── RESERVATIONS
async function loadReservations() {
    try {
        const [resResa, resVilles] = await Promise.all([
            axios.get(`${BASE_URL}/admin/reservations`, { headers }),
            axios.get(`${BASE_URL}/villes`, { headers })
        ]);

        const reservations = resResa.data;
        const villes = resVilles.data;

        const villeMap = {};
        villes.forEach(v => villeMap[v.id] = v.nom);

        document.getElementById("count-reservations").innerText = reservations.length;

        document.getElementById("tbody-reservations").innerHTML = reservations.map(r => {

            const villeDepart = r.trajet ? (villeMap[r.trajet.ville_depart_id] || `Ville ${r.trajet.ville_depart_id}`) : "?";
            const villeArrivee = r.trajet ? (villeMap[r.trajet.ville_arrivee_id] || `Ville ${r.trajet.ville_arrivee_id}`) : "?";

            return `
                <tr class="table-row">
                    <td class="px-6 py-4 text-gray-400 font-mono text-[11px]">#${String(r.id).padStart(4, "0")}</td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-white">${r.user?.prenom || ""} ${r.user?.nom || ""}</div>
                        <div class="text-[11px] text-gray-500">${r.user?.email || ""}</div>
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-600 text-xs">
                        <div class="flex items-center gap-2">
                            <span>${villeDepart}</span>
                            <i class="fas fa-arrow-right text-[10px] text-blue-400/50"></i>
                            <span>${villeArrivee}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs font-medium text-gray-500">${r.nombre_place} Siège(s)</td>
                    <td class="px-6 py-4 font-bold text-blue-600">${r.prix_total} MAD</td>
                    <td class="px-6 py-4">
                        <span class="badge ${r.statut === "confirmed" ? "badge-green" : r.statut === "completed" ? "badge-blue" : "badge-red"}">
                            ${r.statut}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-400 text-xs">${new Date(r.created_at).toLocaleDateString("fr")}</td>
                </tr>
            `;
        }).join("");
    } catch (e) {
        console.error("Erreur lors du chargement des réservations:", e);
    }
}

// ─── TRAJETS
async function loadTrajets() {
    try {
        const [resTrajets, resVilles] = await Promise.all([
            axios.get(`${BASE_URL}/trajets`, { headers }),
            axios.get(`${BASE_URL}/villes`, { headers }),
        ]);

        trajetsData = resTrajets.data;
        villesData = resVilles.data;

        const villeMap = {};
        villesData.forEach((v) => (villeMap[v.id] = v.nom));

        // Pop modal selects
        const opts = villesData
            .map((v) => `<option value="${v.id}">${v.nom}</option>`)
            .join("");
        document.getElementById("trajet-depart").innerHTML =
            '<option value="">Choisir...</option>' + opts;
        document.getElementById("trajet-arrivee").innerHTML =
            '<option value="">Choisir...</option>' + opts;

        document.getElementById("tbody-trajets").innerHTML = trajetsData
            .map(
                (t) => `
                <tr class="table-row">
                    <td class="px-5 py-3 text-gray-500 text-xs">#${t.id}</td>
                    <td class="px-5 py-3 text-white text-xs font-medium">${villeMap[t.ville_depart_id] || t.ville_depart_id}</td>
                    <td class="px-5 py-3 text-white text-xs font-medium">${villeMap[t.ville_arrivee_id] || t.ville_arrivee_id}</td>
                    <td class="px-5 py-3 text-blue-400 text-xs font-bold">${t.prix} MAD</td>
                    <td class="px-5 py-3"><span class="badge ${t.statut === "actif" ? "badge-green" : "badge-red"}">${t.statut}</span></td>
                    <td class="px-5 py-3 flex gap-2">
                        ${t.statut === "actif" ? `<button onclick="cloturerTrajet(${t.id})" class="btn-danger px-3 py-1 text-xs">Clôturer</button>` : ""}
                        <button onclick="supprimerTrajet(${t.id})" class="text-red-500 hover:text-red-300 text-xs px-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `,
            )
            .join("");

        // Pop taxi modal trajet select

        // const optsT = trajetsData
        //     .map(
        //         (t) =>
        //             `<option value="${t.id}">${villeMap[t.ville_depart_id] || t.ville_depart_id} → ${villeMap[t.ville_arrivee_id] || t.ville_arrivee_id}</option>`
        //     )
        //     .join("");
        // document.getElementById("taxi-trajet").innerHTML =
        //     '<option value="">Aucun</option>' + optsT;
    } catch (e) {
        console.error(e);
    }
}

async function remplirTaxiTrajets() {

    // load villes if empty
    if (villesData.length === 0) {
        const resVilles = await axios.get(`${BASE_URL}/villes`, { headers });
        villesData = resVilles.data;
    }

    // load trajets if empty
    if (trajetsData.length === 0) {
        const resTrajets = await axios.get(`${BASE_URL}/trajets`, { headers });
        trajetsData = resTrajets.data;
    }

    const villeMap = {};
    villesData.forEach(v => villeMap[v.id] = v.nom);

    const optsT = trajetsData.map(t => {
        const dep = villeMap[t.ville_depart_id] ?? t.ville_depart_id;
        const arr = villeMap[t.ville_arrivee_id] ?? t.ville_arrivee_id;
        return `<option value="${t.id}">${dep} → ${arr}</option>`;
    }).join("");

    document.getElementById("taxi-trajet").innerHTML =
        `<option value="">Aucun</option>${optsT}`;
}

// ─── TAXIS
async function loadTaxis() {
    try {
        const [resTaxis, resVilles, resUsers] = await Promise.all([
            axios.get(`${BASE_URL}/taxis`, { headers }),
            axios.get(`${BASE_URL}/villes`, { headers }),
            axios.get(`${BASE_URL}/admin/users`, { headers }),
        ]);

        villesData = resVilles.data;
        usersData = resUsers.data;
        const villeMap = {};
        villesData.forEach((v) => (villeMap[v.id] = v.nom));

        const conducteurs = usersData.filter((u) => u.role === "driver");
        const driverOpts = conducteurs
            .map((u) => `<option value="${u.id}">${u.prenom} ${u.nom}</option>`)
            .join("");
        document.getElementById("taxi-driver").innerHTML =
            '<option value="">Choisir...</option>' + driverOpts;

        document.getElementById("tbody-taxis").innerHTML = resTaxis.data
            .map(
                (t) => `
                <tr class="table-row">
                    <td class="px-5 py-3 text-white text-xs font-bold">${t.matricule}</td>
                    <td class="px-5 py-3 text-gray-300 text-xs">${t.driver?.prenom || ""} ${t.driver?.nom || ""}</td>
                    <td class="px-5 py-3 text-gray-300 text-xs">${t.capacite} places</td>
                    <td class="px-5 py-3 text-gray-300 text-xs">${t.trajet ? (villeMap[t.trajet.ville_depart_id] || "?") + " → " + (villeMap[t.trajet.ville_arrivee_id] || "?") : '<span class="text-gray-600">Non assigné</span>'}</td>
                    <td class="px-5 py-3"><span class="badge ${t.statuts === "available" ? "badge-green" : t.statuts === "full" ? "badge-red" : "badge-yellow"}">${t.statuts}</span></td>
                    <td class="px-5 py-3">
                        <button onclick="supprimerTaxi(${t.id})" class="text-red-500 hover:text-red-300 text-xs">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `,
            )
            .join("");
    } catch (e) {
        console.error(e);
    }
}

// ─── VILLES
async function loadVilles() {
    try {
        const res = await axios.get(`${BASE_URL}/villes`, { headers });
        villesData = res.data;

        document.getElementById("grid-villes").innerHTML = villesData
            .map(
                (v) => `
                <div class="card card-hover p-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-600/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-city text-blue-400 text-sm"></i>
                        </div>
                        <span class="font-semibold text-white text-sm">${v.nom}</span>
                    </div>
                    <button onclick="supprimerVille(${v.id})" class="text-gray-600 hover:text-red-400 transition text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `,
            )
            .join("");
    } catch (e) {
        console.error(e);
    }
}

// ─── PENDING DRIVERS
async function loadPendingDrivers() {
    const container = document.getElementById("pending-drivers-section");
    if (!container) return;

    try {
        const res = await axios.get(`${BASE_URL}/admin/users/pending-drivers`, { headers });
        const pending = res.data;

        // Update badge on nav
        const badge = document.getElementById("badge-pending-drivers");
        if (badge) {
            badge.innerText = pending.length;
            badge.style.display = pending.length > 0 ? "inline-flex" : "none";
        }

        if (pending.length === 0) {
            container.innerHTML = `
                <div class="card p-5 mb-6">
                    <h3 class="text-sm font-semibold text-gray-300 mb-1 flex items-center gap-2">
                        <i class="fas fa-user-clock text-amber-400"></i>
                        Conducteurs en attente d'approbation
                    </h3>
                    <p class="text-gray-600 text-xs mt-3">Aucun conducteur en attente.</p>
                </div>`;
            return;
        }

        const rows = pending.map(u => `
            <tr class="table-row">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-600/30 rounded-full flex items-center justify-center text-xs font-bold text-amber-300">
                            ${u.prenom ? u.prenom[0].toUpperCase() : "?"}
                        </div>
                        <div>
                            <p class="text-white text-xs font-semibold">${u.prenom || ""} ${u.nom || ""}</p>
                            <p class="text-gray-500 text-[11px]">${u.email}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-400 text-xs">
                    ${u.driver_profile ? `CNE: ${u.driver_profile.cne}<br>Permis: ${u.driver_profile.permis}` : '<span class="text-gray-600">—</span>'}
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">${new Date(u.created_at).toLocaleDateString("fr")}</td>
                <td class="px-5 py-3 text-right flex gap-2 justify-end">
                    <button onclick="approveDriver(${u.id})"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600/40 transition text-xs font-semibold">
                        <i class="fas fa-check"></i> Approuver
                    </button>
                    <button onclick="rejectDriver(${u.id})"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600/20 text-red-400 hover:bg-red-600/40 transition text-xs font-semibold">
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                </td>
            </tr>
        `).join("");

        container.innerHTML = `
            <div class="card mb-6">
                <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-800">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-user-clock text-amber-400"></i>
                        Conducteurs en attente d'approbation
                        <span class="bg-amber-500 text-black text-[10px] font-bold px-2 py-0.5 rounded-full">
                            ${pending.length}
                        </span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-[11px] text-gray-500 uppercase tracking-wider border-b border-gray-800">
                                <th class="px-5 py-2 text-left">Conducteur</th>
                                <th class="px-5 py-2 text-left">Documents</th>
                                <th class="px-5 py-2 text-left">Inscrit le</th>
                                <th class="px-5 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>`;
    } catch (e) {
        console.error("Erreur loadPendingDrivers:", e);
    }
}

async function approveDriver(id) {
    if (!confirm("Approuver ce conducteur ? Il pourra accéder à son tableau de bord.")) return;
    try {
        await axios.patch(`${BASE_URL}/admin/users/${id}/approve`, {}, { headers });
        await loadPendingDrivers();
        await loadUsers();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur lors de l'approbation");
    }
}

async function rejectDriver(id) {
    if (!confirm("Rejeter ce conducteur ? Son compte sera désactivé.")) return;
    try {
        await axios.patch(`${BASE_URL}/admin/users/${id}/reject`, {}, { headers });
        await loadPendingDrivers();
        await loadUsers();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur lors du rejet");
    }
}

// ─── USERS
async function loadUsers() {
    try {
        const res = await axios.get(`${BASE_URL}/admin/users`, { headers });
        usersData = res.data;

        const roleColors = {
            admin: "badge-yellow",
            driver: "badge-purple",
            user: "badge-blue",
        };

        document.getElementById("tbody-users").innerHTML = usersData.map((u) => {
            const isPending  = u.status === "pending";
            const isInactive = u.status === "inactive";

            // Status badge
            let statusBadge;
            if (isPending)       statusBadge = `<span class="badge" style="background:#2d1f00;color:#fbbf24;">En attente</span>`;
            else if (isInactive) statusBadge = `<span class="badge badge-red">Inactif</span>`;
            else                 statusBadge = `<span class="badge badge-green">Actif</span>`;

            // Actions column
            let actions = "";
            if (u.role !== "admin") {
                if (isPending) {
                    actions = `
                        <button onclick="approveDriver(${u.id})" class="text-emerald-400 hover:text-emerald-300 transition p-2" title="Approuver">
                            <i class="fas fa-user-check"></i>
                        </button>
                        <button onclick="rejectDriver(${u.id})" class="text-red-400 hover:text-red-300 transition p-2" title="Rejeter">
                            <i class="fas fa-user-slash"></i>
                        </button>`;
                } else if (isInactive) {
                    actions = `<button onclick="unbanUser(${u.id})" class="text-green-400 hover:text-green-300 transition p-2" title="Débannir">
                                   <i class="fas fa-user-check"></i>
                               </button>`;
                } else {
                    actions = `<button onclick="banUser(${u.id})" class="text-red-400 hover:text-red-300 transition p-2" title="Bannir">
                                   <i class="fas fa-user-slash"></i>
                               </button>`;
                }
            }

            return `
                <tr class="table-row ${isInactive ? "opacity-60" : ""}">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 ${isPending ? "bg-amber-800" : isInactive ? "bg-red-900" : "bg-blue-600"} rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                ${u.prenom ? u.prenom[0].toUpperCase() : "?"}
                            </div>
                            <div>
                                <p class="text-white text-xs font-medium flex items-center gap-2">
                                    ${u.prenom || ""} ${u.nom || ""}
                                    ${isPending  ? '<span class="text-[10px] text-amber-400 font-bold uppercase">En attente</span>' : ""}
                                    ${isInactive ? '<span class="text-[10px] text-red-400 font-bold uppercase underline">Banni</span>' : ""}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-400 text-xs">${u.email}</td>
                    <td class="px-5 py-3"><span class="badge ${roleColors[u.role] || "badge-blue"}">${u.role}</span></td>
                    <td class="px-5 py-3">${statusBadge}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">${new Date(u.created_at).toLocaleDateString("fr")}</td>
                    <td class="px-5 py-3 text-right flex gap-1 justify-end">${actions}</td>
                </tr>
            `;
        }).join("");
    } catch (e) {
        console.error("Erreur loadUsers:", e);
    }
}

// Action : Bannir
async function banUser(id) {
    if (!confirm("Voulez-vous vraiment bannir cet utilisateur ?")) return;
    try {
        await axios.patch(`${BASE_URL}/admin/users/${id}/ban`, {}, { headers });
        await loadUsers();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur lors du bannissement");
    }
}

// Action : Débannir
async function unbanUser(id) {
    if (!confirm("Voulez-vous réactiver ce compte ?")) return;
    try {
        await axios.patch(`${BASE_URL}/admin/users/${id}/unban`, {}, { headers });
        await loadUsers();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur lors du débannissement");
    }
}

// ─── PAIEMENTS
async function loadPaiements() {
    try {
        const res = await axios.get(`${BASE_URL}/admin/paiements`, { headers });
        document.getElementById("tbody-paiements").innerHTML = res.data
            .map(
                (p) => `
                <tr class="table-row">
                    <td class="px-5 py-3 text-gray-500 text-xs">#${String(p.id).padStart(4, "0")}</td>
                    <td class="px-5 py-3 text-white text-xs">${p.user?.prenom || ""} ${p.user?.nom || ""}</td>
                    <td class="px-5 py-3 text-blue-400 text-xs font-bold">${p.montant} MAD</td>
                    <td class="px-5 py-3 text-gray-400 text-xs uppercase">${p.methode}</td>
                    <td class="px-5 py-3"><span class="badge ${p.statut === "paid" ? "badge-green" : "badge-red"}">${p.statut}</span></td>
                    <td class="px-5 py-3 text-gray-500 text-xs font-mono">${p.stripe_payment_intent_id?.slice(0, 20) || "N/A"}...</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">${new Date(p.created_at).toLocaleDateString("fr")}</td>
                </tr>
            `,
            )
            .join("");
    } catch (e) {
        console.error(e);
    }
}

// ─── ACTIONS
async function creerVille() {
    const nom = document.getElementById("ville-nom").value.trim();
    if (!nom) return;
    try {
        await axios.post(`${BASE_URL}/admin/villes`, { nom }, { headers });
        closeModal("modal-ville");
        document.getElementById("ville-nom").value = "";
        await loadVilles();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

async function supprimerVille(id) {
    if (!confirm("Supprimer cette ville ?")) return;
    try {
        await axios.delete(`${BASE_URL}/admin/villes/${id}`, { headers });
        await loadVilles();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

async function creerTrajet() {
    const data = {
        ville_depart_id: document.getElementById("trajet-depart").value,
        ville_arrivee_id: document.getElementById("trajet-arrivee").value,
        prix: document.getElementById("trajet-prix").value,
    };
    try {
        await axios.post(`${BASE_URL}/admin/trajets`, data, { headers });
        closeModal("modal-trajet");
        await loadTrajets();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

async function cloturerTrajet(id) {
    if (!confirm("Clôturer ce trajet ?")) return;
    try {
        await axios.patch(
            `${BASE_URL}/admin/trajets/${id}/cloturer`,
            {},
            { headers },
        );
        await loadTrajets();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

async function supprimerTrajet(id) {
    if (!confirm("Supprimer ce trajet ?")) return;
    try {
        await axios.delete(`${BASE_URL}/admin/trajets/${id}`, { headers });
        await loadTrajets();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

async function creerTaxi() {
    const data = {
        matricule: document.getElementById("taxi-matricule").value,
        capacite: document.getElementById("taxi-capacite").value,
        driver_id: document.getElementById("taxi-driver").value,
        trajet_id: document.getElementById("taxi-trajet").value || null,
        statuts: "available",
    };
    try {
        await axios.post(`${BASE_URL}/admin/taxis`, data, { headers });
        closeModal("modal-taxi");
        await loadTaxis();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

async function supprimerTaxi(id) {
    if (!confirm("Supprimer ce taxi ?")) return;
    try {
        await axios.delete(`${BASE_URL}/admin/taxis/${id}`, { headers });
        await loadTaxis();
    } catch (e) {
        alert(e.response?.data?.message || "Erreur");
    }
}

// ─── MODALS
function openModal(id) {
    document.getElementById(id).classList.remove("hidden");

    if (id === "modal-taxi") {
        remplirTaxiTrajets();
    }
}
function closeModal(id) {
    document.getElementById(id).classList.add("hidden");
}

// ─── MISC
function refreshData() {
    const activeTab = tabs.find(
        (t) =>
            !document.getElementById("tab-" + t).classList.contains("hidden"),
    );
    if (activeTab) loadTab(activeTab);
}

async function logout() {
    try {
        await axios.post(`${BASE_URL}/logout`, {}, { headers });
    } catch (e) {}
    localStorage.removeItem("api_token");
    localStorage.removeItem("user");
    window.location.href = "/login";
}

showTab('dashboard');
