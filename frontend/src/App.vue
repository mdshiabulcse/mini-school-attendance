
<template>
  <v-app>
    <AppBar v-if="isAuthenticated" />

    <Sidebar v-if="isAuthenticated" />

    <v-main>
      <router-view />
    </v-main>
    <NotificationContainer />

    <Footer v-if="isAuthenticated" />
  </v-app>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppBar from '@/components/Layout/AppBar.vue'
import Sidebar from '@/components/Layout/Sidebar.vue'
import Footer from '@/components/Layout/Footer.vue'
import NotificationContainer from '@/components/Common/NotificationContainer.vue'

const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)

onMounted(() => {
  authStore.initializeAuth()
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
  font-family: 'Roboto', sans-serif;
}
</style>
