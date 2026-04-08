<template>
  <div class="login-container">
    <div class="login-card">
      <div class="header">
        <h2>Welcome Back</h2>
        <p class="subtitle">Please enter your credentials to login.</p>
      </div>

      <form @submit.prevent="login" class="form-group">
        <div class="input-wrapper">
          <input v-model="email" type="email" placeholder="Email Address" required />
        </div>

        <div class="input-wrapper">
          <input v-model="password" type="password" placeholder="Password" required />
        </div>

        <button type="submit" class="login-btn">Login</button>
      </form>

      <transition name="fade">
        <p v-if="message" :class="['status-msg', success ? 'success' : 'error']">
          {{ message }}
        </p>
      </transition>

      <div class="footer">
        <p>Don't have an account? <a href="/register">Sign up</a></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "../services/api";

const email = ref("");
const password = ref("");
const message = ref("");
const success = ref(false);

const login = async () => {
  try {
    const res = await api.post("/api/login", {
      email: email.value,
      password: password.value
    });

    localStorage.setItem("token", res.data.access_token);
    message.value = "Login successful! 🎉";
    success.value = true;

    // Optional: redirect to dashboard after 1 second
    // setTimeout(() => router.push('/dashboard'), 1000);

  } catch (err) {
    message.value = err.response?.data?.message || "Invalid email or password ❌";
    success.value = false;
  }
};
</script>

<style scoped>
/* Main Container */
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  padding: 20px;
}

/* Card Design */
.login-card {
  background: white;
  padding: 2.5rem;
  border-radius: 16px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 380px;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

h2 {
  margin: 0;
  color: #2d3436;
  font-size: 1.75rem;
  font-weight: 700;
}

.subtitle {
  color: #636e72;
  font-size: 0.9rem;
  margin-top: 8px;
}

/* Form Styling */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #dfe6e9;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
  background-color: #f9f9f9;
}

input:focus {
  outline: none;
  border-color: #0984e3;
  background-color: #fff;
  box-shadow: 0 0 0 4px rgba(9, 132, 227, 0.1);
}

/* Button */
.login-btn {
  background-color: #0984e3;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.1s ease, background 0.2s ease;
  margin-top: 0.5rem;
}

.login-btn:hover {
  background-color: #0873c4;
}

.login-btn:active {
  transform: scale(0.98);
}

/* Status Messages */
.status-msg {
  margin-top: 1.5rem;
  padding: 12px;
  border-radius: 8px;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 500;
}

.success {
  background-color: #e3fcef;
  color: #00875a;
}

.error {
  background-color: #ffebe6;
  color: #de350b;
}

/* Footer Link */
.footer {
  margin-top: 2rem;
  text-align: center;
}

.footer p {
  font-size: 0.85rem;
  color: #636e72;
}

.footer a {
  color: #0984e3;
  text-decoration: none;
  font-weight: 600;
}

.footer a:hover {
  text-decoration: underline;
}

/* Animation */
.fade-enter-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from {
  opacity: 0;
}
</style>
