<template>
  <div>
    <h2>Login</h2>
    <input v-model="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button @click="login">Login</button>
    <p>{{ message }}</p>
  </div>
</template>

<script>
import api from "../services/api";

export default {
  data() {
    return {
      email: "",
      password: "",
      message: "",
    };
  },
  methods: {
    async login() {
      try {
        // 1) Get CSRF cookie
        await api.get("/sanctum/csrf-cookie");

        // 2) Login request
        const res = await api.post("/api/login", {
          email: this.email,
          password: this.password,
        });

        this.message = "Login successful ✅";
        console.log("Login response:", res.data);

      } catch (err) {
        console.log(err.response?.data || err.message);
        this.message = "Login failed ❌";
      }
    },
  },
};
</script>
