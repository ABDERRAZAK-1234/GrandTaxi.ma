<template>
  <div class="register-container">
    <div class="register-card">
      <h2>Create Account</h2>
      <p class="subtitle">Join us today! Please enter your details.</p>

      <form @submit.prevent="register" class="form-group">
        <div class="name-row">
          <input v-model="nom" placeholder="Nom" required />
          <input v-model="prenom" placeholder="Prenom" required />
        </div>

        <input v-model="email" type="email" placeholder="Email Address" required />
        <input v-model="password" type="password" placeholder="Password" required />
        <input v-model="password_confirmation" type="password" placeholder="Confirm Password" required />

        <button type="submit" class="register-btn">Sign Up</button>
      </form>

      <transition name="fade">
        <p v-if="message" :class="['status-msg', success ? 'success' : 'error']">
          {{ message }}
        </p>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "../services/api";

const nom = ref("");
const prenom = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const message = ref("");
const success = ref(false);

const register = async () => {
  try {
    const res = await api.post("/api/register", {
      nom: nom.value,
      prenom: prenom.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value
    });

    localStorage.setItem("token", res.data.access_token);
    message.value = "Account created successfully! 🎉";
    success.value = true;

    // Reset fields
    nom.value = prenom.value = email.value = password.value = password_confirmation.value = "";

  } catch (err) {
    message.value = err.response?.data?.message || "Registration failed ❌";
    success.value = false;
  }
};
</script>

<style scoped>
/* Container & Background */
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: 'Inter', sans-serif;
  padding: 20px;
}

/* Card Styling */
.register-card {
  background: white;
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

h2 {
  margin-bottom: 0.5rem;
  color: #333;
  font-size: 1.8rem;
}

.subtitle {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

/* Form Elements */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.name-row {
  display: flex;
  gap: 10px;
}

input {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #4a90e2;
  box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}

/* Button */
.register-btn {
  background-color: #4a90e2;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease, transform 0.1s ease;
  margin-top: 10px;
}

.register-btn:hover {
  background-color: #357abd;
}

.register-btn:active {
  transform: scale(0.98);
}

/* Feedback Messages */
.status-msg {
  margin-top: 1.5rem;
  padding: 10px;
  border-radius: 6px;
  font-size: 0.9rem;
}

.success {
  background-color: #e6fffa;
  color: #2c7a7b;
}

.error {
  background-color: #fff5f5;
  color: #c53030;
}

/* Simple Fade Animation */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
