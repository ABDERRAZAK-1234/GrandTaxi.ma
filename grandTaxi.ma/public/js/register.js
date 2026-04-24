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

async function register() {
    document.getElementById("error-msg").classList.add("hidden");
    document.getElementById("success-msg").classList.add("hidden");

    const data = {
        nom: document.getElementById("nom").value,
        prenom: document.getElementById("prenom").value,
        email: document.getElementById("email").value,
        password: document.getElementById("password").value,
        password_confirmation: document.getElementById("password_confirmation")
            .value,
        role: role,
    };

    if (role === "driver") {
        data.cne = document.getElementById("cne").value;
        data.permis = document.getElementById("permis").value;
        data.taxi_matricule = document.getElementById("taxi_matricule").value;
        data.taxi_capacite = parseInt(
            document.getElementById("taxi_capacite").value,
        );
    }

    try {
        const res = await axios.post("/api/register", data);

        localStorage.setItem("api_token", res.data.access_token);
        localStorage.setItem("user", JSON.stringify(res.data.user));
        localStorage.removeItem("register_role");

        document.getElementById("success-msg").innerText =
            "✅ Compte créé avec succès ! Redirection...";
        document.getElementById("success-msg").classList.remove("hidden");

        setTimeout(() => {
            window.location.href = "/index";
        }, 1500);
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
