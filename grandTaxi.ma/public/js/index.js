const BASE_URL = "/api";
let token = localStorage.getItem("api_token");
let user = JSON.parse(localStorage.getItem("user") || "null");
let trajetSelectionne = null;
let taxiSelectionneId = null;

// ─── POLLING ──────────────────────────────────────────────
let pollingInterval = null;

function startPolling(taxiId) {
    stopPolling();
    pollingInterval = setInterval(async () => {
        await chargerSiegesOccupes(taxiId);
    }, 3000);
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// ─── REVERB ───────────────────────────────────────────────
function ecouterTrajet(trajetId, taxiId) {
    if (!window.pusherInstance) return;

    const channelName = "trajets." + trajetId;
    window.pusherInstance.unsubscribe(channelName);

    const channel = window.pusherInstance.subscribe(channelName);

    channel.bind("reservation.created", function (data) {
        console.log("🎉 Siège réservé en temps réel:", data);

        // Vérifier que c'est le même taxi
        if (data.taxi_id != taxiId) return;

        // Parser les sièges
        let sieges = data.sieges || [];
        if (typeof sieges === "string") {
            try { sieges = JSON.parse(sieges); } catch (e) { sieges = []; }
        }

        // Marquer les sièges comme occupés
        sieges.forEach((num) => {
            const id    = "s" + num;
            const rect  = document.getElementById(id);
            const label = document.getElementById(id + "-label");

            if (rect && !rect.classList.contains("taken")) {
                rect.classList.remove("selected");
                rect.classList.add("taken");
                siegesSelectionnes.delete(id);
            }
            if (label) {
                label.classList.remove("selected");
                label.classList.add("taken");
            }

            siegesPris.add(id);
        });

        updateSiegeUI();

        // Toast notification
        afficherToast(
            `⚠️ Le(s) siège(s) S${sieges.join(", S")} vien(nen)t d'être réservé(s) !`
        );
    });

    console.log("👂 Écoute Reverb sur canal:", channelName, "taxi:", taxiId);
}

function arreterEcoute(trajetId) {
    if (!window.pusherInstance) return;
    window.pusherInstance.unsubscribe("trajets." + trajetId);
}

// ─── TOAST ────────────────────────────────────────────────
function afficherToast(msg) {
    const toast = document.createElement("div");
    toast.className =
        "fixed top-5 right-5 z-50 bg-orange-500 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold flex items-center gap-2";
    toast.style.transition = "all 0.3s";
    toast.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ─── SIEGES ───────────────────────────────────────────────
const siegesSelectionnes = new Set();
const siegesPris = new Set();

function toggleSiege(id, num) {
    if (siegesPris.has(id)) return;
    const rect  = document.getElementById(id);
    const label = document.getElementById(id + "-label");

    if (siegesSelectionnes.has(id)) {
        siegesSelectionnes.delete(id);
        rect.classList.remove("selected");
        label.classList.remove("selected");
    } else {
        siegesSelectionnes.add(id);
        rect.classList.add("selected");
        label.classList.add("selected");
    }
    updateSiegeUI();
}

function updateSiegeUI() {
    const nums = [...siegesSelectionnes].map((s) => "S" + s.replace("s", ""));
    document.getElementById("sieges-display").innerText = nums.length
        ? nums.join(", ")
        : "Aucun";

    const prix      = trajetSelectionne ? parseFloat(trajetSelectionne.prix) : 0;
    const hasBagage = document.getElementById("bagage").checked;
    const nbrBagage = hasBagage
        ? parseInt(document.getElementById("nombre-bagage").value) || 0
        : 0;
    const total = prix * siegesSelectionnes.size + 10 * nbrBagage;
    document.getElementById("prix-total").innerText = total + " MAD";

    const btn = document.getElementById("btn-payer");
    btn.disabled = siegesSelectionnes.size === 0;
}

function marquerSiegesPris(siegesOccupes) {
    // Reset d'abord les anciens taken (sans toucher selected)
    siegesPris.forEach((id) => {
        const rect  = document.getElementById(id);
        const label = document.getElementById(id + "-label");
        if (rect)  rect.classList.remove("taken");
        if (label) label.classList.remove("taken");
    });
    siegesPris.clear();

    siegesOccupes.forEach((num) => {
        const id    = "s" + num;
        siegesPris.add(id);
        const rect  = document.getElementById(id);
        const label = document.getElementById(id + "-label");
        if (rect)  rect.classList.add("taken");
        if (label) label.classList.add("taken");

        // Si ce siège était sélectionné, le désélectionner
        if (siegesSelectionnes.has(id)) {
            siegesSelectionnes.delete(id);
            if (rect)  rect.classList.remove("selected");
            if (label) label.classList.remove("selected");
        }
    });

    updateSiegeUI();
}

function resetSieges() {
    siegesSelectionnes.forEach((id) => {
        const rect  = document.getElementById(id);
        const label = document.getElementById(id + "-label");
        if (rect)  rect.classList.remove("selected");
        if (label) label.classList.remove("selected");
    });
    siegesSelectionnes.clear();

    siegesPris.forEach((id) => {
        const rect  = document.getElementById(id);
        const label = document.getElementById(id + "-label");
        if (rect)  rect.classList.remove("taken");
        if (label) label.classList.remove("taken");
    });
    siegesPris.clear();
}

// ─── NAVBAR ───────────────────────────────────────────────
function initNavbar() {
    if (token && user) {
        document.getElementById("nav-guest").classList.add("hidden");
        const navUser = document.getElementById("nav-user");
        navUser.classList.remove("hidden");
        navUser.classList.add("flex");
        document.getElementById("nav-username").innerText =
            user.prenom + " " + user.nom;
    }
}

async function logout() {
    try {
        await axios.post(`${BASE_URL}/logout`, {}, {
            headers: { Authorization: `Bearer ${token}` },
        });
    } catch (e) {}
    localStorage.removeItem("api_token");
    localStorage.removeItem("user");
    window.location.href = "/login";
}

// ─── VILLES ───────────────────────────────────────────────
async function chargerVilles() {
    try {
        const res    = await axios.get(`${BASE_URL}/villes`);
        const villes = res.data;
        const depart  = document.getElementById("ville-depart");
        const arrivee = document.getElementById("ville-arrivee");

        villes.forEach((v) => {
            depart.innerHTML  += `<option value="${v.id}">${v.nom}</option>`;
            arrivee.innerHTML += `<option value="${v.id}">${v.nom}</option>`;
        });
    } catch (e) {
        console.error("Erreur chargement villes", e);
    }
}

// ─── RECHERCHER TRAJETS ───────────────────────────────────
async function rechercherTrajets() {
    const villeDepart  = document.getElementById("ville-depart").value;
    const villeArrivee = document.getElementById("ville-arrivee").value;

    if (!villeDepart || !villeArrivee) {
        alert("Veuillez sélectionner une ville de départ et d'arrivée.");
        return;
    }
    if (villeDepart === villeArrivee) {
        alert("La ville de départ et d'arrivée doivent être différentes.");
        return;
    }

    document.getElementById("empty-state").classList.add("hidden");
    document.getElementById("no-results").classList.add("hidden");
    document.getElementById("trajets-grid").classList.add("hidden");
    document.getElementById("loading").classList.remove("hidden");

    try {
        const resTrajets = await axios.get(`${BASE_URL}/trajets`);
        const trajets = resTrajets.data.filter(
            (t) =>
                t.ville_depart_id == villeDepart &&
                t.ville_arrivee_id == villeArrivee &&
                t.statut !== "cloture"
        );

        document.getElementById("loading").classList.add("hidden");

        const nomDepart  = document.getElementById("ville-depart").options[document.getElementById("ville-depart").selectedIndex].text;
        const nomArrivee = document.getElementById("ville-arrivee").options[document.getElementById("ville-arrivee").selectedIndex].text;

        const label = document.getElementById("search-label");
        label.innerText = `${nomDepart} → ${nomArrivee}`;
        label.classList.remove("hidden");

        if (trajets.length === 0) {
            document.getElementById("no-results").classList.remove("hidden");
            return;
        }

        const tousLesTaxis = [];
        for (const trajet of trajets) {
            const resTaxis = await axios.get(`${BASE_URL}/trajets/${trajet.id}/taxis`);
            resTaxis.data.forEach((taxi) => {
                tousLesTaxis.push({ ...taxi, trajet, nomDepart, nomArrivee });
            });
        }

        if (tousLesTaxis.length === 0) {
            document.getElementById("no-results").classList.remove("hidden");
            return;
        }

        afficherTaxis(tousLesTaxis);
    } catch (e) {
        document.getElementById("loading").classList.add("hidden");
        console.error("Erreur", e);
    }
}

// ─── AFFICHER LES TAXIS ───────────────────────────────────
function afficherTaxis(taxis) {
    const grid = document.getElementById("trajets-grid");
    grid.innerHTML = "";

    taxis.forEach((taxi) => {
        const placesRestantes = taxi.places_restantes;
        const isFull = placesRestantes === 0;

        let placesHTML = '<div class="flex gap-1 mt-1">';
        for (let i = 1; i <= taxi.capacite; i++) {
            const pris = i > placesRestantes;
            placesHTML += `<div class="w-4 h-4 rounded-sm ${pris ? "bg-red-400" : "bg-green-400"}" title="${pris ? "Occupé" : "Libre"}"></div>`;
        }
        placesHTML += "</div>";

        const card = `
            <div class="gt-card p-6 hover:shadow-xl transition-all duration-300 group ${isFull ? "opacity-60" : ""}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-xs font-bold ${isFull ? "gt-badge-red-light" : "gt-badge-green-light"} px-2 py-1 rounded mb-2 inline-block uppercase">
                            ${isFull ? "Complet" : placesRestantes + " place(s) disponible(s)"}
                        </span>
                        <div class="text-lg font-bold flex items-center gap-2 flex-wrap" style="color: var(--gt-text);">
                            ${taxi.nomDepart} <i class="fas fa-arrow-right text-sm text-gray-400"></i> ${taxi.nomArrivee}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold" style="color: var(--gt-red);">${taxi.trajet.prix} MAD</span>
                        <p class="text-xs" style="color: var(--gt-text-light);">par place</p>
                    </div>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center" style="color: var(--gt-text-muted);">
                        <i class="fas fa-taxi w-6" style="color: var(--gt-red);"></i>
                        <span class="text-sm font-medium">${taxi.matricule}</span>
                    </div>
                    <div class="flex items-center" style="color: var(--gt-text-muted);">
                        <i class="fas fa-chair w-6" style="color: var(--gt-green);"></i>
                        <div><span class="text-sm">Places :</span>${placesHTML}</div>
                    </div>
                    <div class="flex items-center" style="color: var(--gt-text-muted);">
                        <i class="fas fa-briefcase w-6" style="color: var(--gt-gold);"></i>
                        <span class="text-sm">Bagage: <span class="font-semibold" style="color: var(--gt-green);">+10 MAD</span></span>
                    </div>
                </div>
                <button
                    onclick='ouvrirModal(${JSON.stringify(taxi.trajet)}, "${taxi.nomDepart}", "${taxi.nomArrivee}", ${taxi.id})'
                    ${isFull ? "disabled" : ""}
                    class="w-full py-3 font-bold rounded-xl transition duration-300 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    style="border: 2px solid var(--gt-red); color: var(--gt-red);"
                    onmouseover="if(!this.disabled){this.style.background='var(--gt-red)';this.style.color='white';}"
                    onmouseout="this.style.background='transparent';this.style.color='var(--gt-red)';">
                    <i class="fas fa-chair"></i>
                    ${isFull ? "Taxi complet" : "Choisir mes sièges"}
                </button>
            </div>
        `;
        grid.innerHTML += card;
    });

    grid.classList.remove("hidden");
    document.getElementById("trajets").scrollIntoView({ behavior: "smooth" });
}

// ─── MODAL ────────────────────────────────────────────────
async function ouvrirModal(trajet, nomDepart, nomArrivee, taxiId = null) {
    if (!token) {
        window.location.href = "/login";
        return;
    }

    trajetSelectionne = trajet;
    taxiSelectionneId = taxiId;
    resetSieges();

    document.getElementById("modal-trajet-label").innerText = `${nomDepart} → ${nomArrivee}`;
    document.getElementById("modal-prix").innerText          = trajet.prix;
    document.getElementById("modal-error").classList.add("hidden");
    document.getElementById("modal-success").classList.add("hidden");
    document.getElementById("bagage").checked = false;
    document.getElementById("bagage-count").classList.add("hidden");
    document.getElementById("sieges-display").innerText = "Aucun";
    document.getElementById("prix-total").innerText     = "0 MAD";
    document.getElementById("btn-payer").disabled       = true;

    const selectTaxi = document.getElementById("select-taxi");
    selectTaxi.innerHTML = '<option value="">Chargement...</option>';

    try {
        const res   = await axios.get(`${BASE_URL}/trajets/${trajet.id}/taxis`);
        const taxis = res.data;

        if (taxis.length === 0) {
            selectTaxi.innerHTML = '<option value="">Aucun taxi disponible</option>';
        } else {
            selectTaxi.innerHTML = taxis.map((t) =>
                `<option value="${t.id}" ${t.id == taxiId ? "selected" : ""} data-places="${t.places_restantes}">
                    ${t.matricule} — ${t.places_restantes} places restantes
                </option>`
            ).join("");

            const selectedId = taxiId || taxis[0].id;
            taxiSelectionneId = selectedId;

            // Charger sièges occupés
            await chargerSiegesOccupes(selectedId);

            // Démarrer polling
            startPolling(selectedId);

            // Démarrer écoute Reverb temps réel
            ecouterTrajet(trajet.id, selectedId);
        }
    } catch (e) {
        selectTaxi.innerHTML = '<option value="">Erreur chargement taxis</option>';
    }

    document.getElementById("modal-reservation").classList.remove("hidden");
}

async function chargerSiegesOccupes(taxiId) {
    try {
        const res = await axios.get(`${BASE_URL}/taxis/${taxiId}/sieges-occupes`);
        marquerSiegesPris(res.data.sieges_occupes || []);
    } catch (e) {}
}

function fermerModal() {
    document.getElementById("modal-reservation").classList.add("hidden");
    resetSieges();
    stopPolling();

    // Arrêter écoute Reverb
    if (trajetSelectionne) {
        arreterEcoute(trajetSelectionne.id);
    }
}

// ─── BAGAGE ───────────────────────────────────────────────
function toggleBagage() {
    const checked = document.getElementById("bagage").checked;
    document.getElementById("bagage-count").classList.toggle("hidden", !checked);
    updateSiegeUI();
}

// ─── PAIEMENT ─────────────────────────────────────────────
async function initierPaiement() {
    const taxiId    = document.getElementById("select-taxi").value;
    const hasBagage = document.getElementById("bagage").checked;
    const nbrBagage = hasBagage
        ? parseInt(document.getElementById("nombre-bagage").value)
        : 0;

    if (!taxiId) {
        afficherErreurModal("Veuillez sélectionner un taxi.");
        return;
    }
    if (siegesSelectionnes.size === 0) {
        afficherErreurModal("Veuillez sélectionner au moins un siège.");
        return;
    }

    const siegesNums = [...siegesSelectionnes].map((s) =>
        parseInt(s.replace("s", ""))
    );

    try {
        const res = await axios.post(
            `${BASE_URL}/reserver`,
            {
                trajet_id:     trajetSelectionne.id,
                taxi_id:       taxiId,
                nombre_place:  siegesSelectionnes.size,
                sieges:        siegesNums,
                bagage:        hasBagage,
                nombre_bagage: nbrBagage,
            },
            { headers: { Authorization: `Bearer ${token}` } }
        );

        localStorage.setItem("client_secret", res.data.client_secret);
        localStorage.setItem("montant", res.data.montant);
        window.location.href = "/paiement";
    } catch (err) {
        const msg = err.response?.data?.message || "Erreur lors de la réservation";
        afficherErreurModal(msg);
    }
}

// ─── HELPERS ──────────────────────────────────────────────
function afficherErreurModal(msg) {
    const el = document.getElementById("modal-error");
    el.innerText = msg;
    el.classList.remove("hidden");
    document.getElementById("modal-success").classList.add("hidden");
}

function afficherSuccesModal(msg) {
    const el = document.getElementById("modal-success");
    el.innerText = msg;
    el.classList.remove("hidden");
    document.getElementById("modal-error").classList.add("hidden");
}

// ─── INIT ─────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
    chargerVilles();

    document.getElementById("bagage").addEventListener("change", toggleBagage);
    document.getElementById("nombre-bagage").addEventListener("input", updateSiegeUI);

    document.getElementById("select-taxi").addEventListener("change", async function () {
        if (this.value) {
            taxiSelectionneId = this.value;
            resetSieges();
            await chargerSiegesOccupes(this.value);
            startPolling(this.value);

            // Changer l'écoute Reverb pour le nouveau taxi
            if (trajetSelectionne) {
                ecouterTrajet(trajetSelectionne.id, this.value);
            }
        }
    });

    document.getElementById("modal-reservation").addEventListener("click", function (e) {
        if (e.target === this) fermerModal();
    });
});
