<template>
  <header>
    <img alt="Vue logo" class="logo" src="@/assets/logo.svg" width="125" height="125" />

    <div class="wrapper">
      <HelloWorld msg="You did it!" />

      <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
      </nav>
    </div>
  </header>

  <!-- Body content -->
  <main>
    <RegisterForm />
    <RouterView />
  </main>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'
import RegisterForm from "./components/RegisterForm.vue";
</script>

<style scoped>
/* خلي styles ديالك ديال header بحال ما هو */
header {
  line-height: 1.5;
  max-height: 100vh;
}

/* Style لل register form باش ما يدخلش ف header */
main {
  max-width: 600px;
  margin: 2rem auto;
  padding: 1rem;
}
</style>
