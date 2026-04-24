let roleSelectionne = "user";

        function selectRole(role) {
            roleSelectionne = role;

            const roles = ["user", "driver"];

            roles.forEach((r) => {
                const card = document.getElementById("card-" + r);
                const radio = document.getElementById("radio-" + r);
                const title = document.getElementById("title-" + r);

                if (!card || !radio || !title) return;

                if (r === role) {
                    card.classList.add("active");
                    card.classList.remove("border-gray-200");
                    card.classList.add("border-blue-600", "bg-blue-50");

                    title.classList.add("text-blue-700");
                    title.classList.remove("text-gray-600");

                    radio.classList.add("border-blue-600", "bg-blue-600");
                    radio.classList.remove("border-gray-300");
                } else {
                    card.classList.remove("active", "border-blue-600", "bg-blue-50");
                    card.classList.add("border-gray-200", "bg-white");

                    title.classList.remove("text-blue-700");
                    title.classList.add("text-gray-600");

                    radio.classList.remove("border-blue-600", "bg-blue-600");
                    radio.classList.add("border-gray-300");
                }
            });
        }

        function continuer() {
            localStorage.setItem("register_role", roleSelectionne);
            window.location.href = "/register";
        }
