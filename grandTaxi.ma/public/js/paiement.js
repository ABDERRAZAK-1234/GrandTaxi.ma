const BASE_URL = "/api";
const token = localStorage.getItem("api_token");
const clientSecret = localStorage.getItem("client_secret");
const montant = localStorage.getItem("montant");

document.getElementById("montant-display").innerText = montant
    ? montant + " MAD"
    : "-- MAD";

if (!clientSecret) {
    window.location.href = "/index";
}

const stripe = Stripe(window.STRIPE_KEY);
const elements = stripe.elements();

const cardElement = elements.create("card", {
    style: {
        base: {
            fontSize: "16px",
            color: "#374151",
            "::placeholder": { color: "#9ca3af" },
        },
    },
});

cardElement.mount("#card-element");

cardElement.on("change", (event) => {
    const display = document.getElementById("card-errors");
    display.innerText = event.error ? event.error.message : "";
});

async function payerStripe() {
    const btn = document.getElementById("btn-payer");
    const btnText = document.getElementById("btn-text");

    btn.disabled = true;
    btnText.innerText = "Traitement en cours...";

    const { paymentIntent, error } = await stripe.confirmCardPayment(
        clientSecret,
        {
            payment_method: { card: cardElement },
        },
    );

    if (error) {
        document.getElementById("error-msg").innerText = error.message;
        document.getElementById("error-msg").classList.remove("hidden");
        btn.disabled = false;
        btnText.innerText = "Payer maintenant";
        return;
    }

    if (paymentIntent.status === "succeeded") {
        document.getElementById("success-msg").innerText =
            "✅ Paiement accepté ! Confirmation en cours...";
        document.getElementById("success-msg").classList.remove("hidden");

        try {
            const res = await axios.post(
                // ← ici le fix : const res =
                `${BASE_URL}/reserver/confirmer`,
                { payment_intent_id: paymentIntent.id },
                { headers: { Authorization: `Bearer ${token}` } },
            );

            localStorage.removeItem("client_secret");
            localStorage.removeItem("montant");

            const reservationId = res.data.reservation.id;

            document.getElementById("success-msg").innerText =
                "Réservation confirmée ! Téléchargement du billet...";

            // Télécharger le billet
            const link = document.createElement("a");
            link.href = `/api/reservations/${reservationId}/billet?token=${token}`;
            link.download = `billet-grandtaxi-${reservationId}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            setTimeout(() => {
                window.location.href = "/index";
            }, 3000);
        } catch (err) {
            console.log("Erreur backend:", err.response?.data);
            document.getElementById("error-msg").innerText =
                err.response?.data?.message ||
                "Erreur lors de la confirmation.";
            document.getElementById("error-msg").classList.remove("hidden");
            document.getElementById("success-msg").classList.add("hidden");
            btn.disabled = false;
            btnText.innerText = "Payer maintenant";
        }
    }
}
