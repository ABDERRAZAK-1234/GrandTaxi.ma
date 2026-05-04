const role = localStorage.getItem("register_role") || "user";

if (role === "driver") {
    document.getElementById("subtitle").innerText =
        "Créez votre compte conducteur";
    document.getElementById("role-icon").innerText = "🚕";
    document.getElementById("role-text").innerText = "Conducteur";
    document.getElementById("role-sub").innerText =
        "Vous pourrez proposer des trajets";
    document.getElementById("driver-fields").classList.remove("hidden");
} else {
    document.getElementById("subtitle").innerText =
        "Créez votre compte voyageur";
    document.getElementById("role-icon").innerText = "👤";
    document.getElementById("role-text").innerText = "Voyageur";
    document.getElementById("role-sub").innerText =
        "Vous pourrez réserver des places";
}

// Fetch trajets if driver
if (role === "driver") {
    axios.get("/api/trajets").then(res => {
        const select = document.getElementById("taxi_trajet");
        const trajets = res.data.data || res.data;
        trajets.forEach(t => {
            const dep = t.ville_depart ? t.ville_depart.nom : 'Inconnu';
            const arr = t.ville_arrivee ? t.ville_arrivee.nom : 'Inconnu';
            const opt = document.createElement("option");
            opt.value = t.id;
            opt.innerText = `${dep} ➔ ${arr}`;
            select.appendChild(opt);
        });
    }).catch(console.error);
}

async function register() {
    document.getElementById("error-msg").classList.add("hidden");
    document.getElementById("success-msg").classList.add("hidden");

    const formData = new FormData();
    formData.append("nom", document.getElementById("nom").value);
    formData.append("prenom", document.getElementById("prenom").value);
    formData.append("email", document.getElementById("email").value);
    formData.append("password", document.getElementById("password").value);
    formData.append("password_confirmation", document.getElementById("password_confirmation").value);
    formData.append("role", role);

    if (role === "driver") {
        formData.append("cne", document.getElementById("cne").value);
        formData.append("permis", document.getElementById("permis").value);
        formData.append("taxi_matricule", document.getElementById("taxi_matricule").value);
        formData.append("taxi_capacite", document.getElementById("taxi_capacite").value);
        
        const trajetId = document.getElementById("taxi_trajet").value;
        if (trajetId) formData.append("taxi_trajet", trajetId);

        const imageFile = document.getElementById("taxi_image").files[0];
        if (imageFile) {
            formData.append("taxi_image", imageFile);
        }
    }

    try {
        const res = await axios.post("/api/register", formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        localStorage.setItem("api_token", res.data.access_token);
        localStorage.setItem("user", JSON.stringify(res.data.user));
        localStorage.removeItem("register_role");

        if (role === 'driver') {
            // Drivers must wait for admin approval — redirect to a pending page
            document.getElementById("success-msg").innerHTML =
                `✅ Compte conducteur créé !<br>
                 <span style="font-size:0.85em; opacity:0.85;">
                   Votre compte est en attente de validation par un administrateur.
                   Vous recevrez l'accès à votre tableau de bord une fois approuvé.
                 </span>`;
            document.getElementById("success-msg").classList.remove("hidden");

            // Disable the submit button so they don't re-submit
            document.querySelector("button[onclick='register()']") &&
                (document.querySelector("button[onclick='register()']").disabled = true);

            // After a moment, redirect to login so they see the pending message there
            setTimeout(() => {
                window.location.href = '/login?pending=1';
            }, 4000);
        } else {
            document.getElementById("success-msg").innerText =
                "✅ Compte créé avec succès ! Redirection...";
            document.getElementById("success-msg").classList.remove("hidden");

            setTimeout(() => {
                window.location.href = '/index';
            }, 1500);
        }
    } catch (err) {
        const errors = err.response?.data?.errors;
        const msg = errors
            ? Object.values(errors).flat().join("\n")
            : err.response?.data?.message || "Erreur";

        const el = document.getElementById("error-msg");
        el.innerText = msg;
        el.classList.remove("hidden");
    }
}
