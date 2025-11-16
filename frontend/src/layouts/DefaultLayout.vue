<!-- src/layouts/DefaultLayout.vue -->
<template>
  <v-layout>
    <!-- Sidebar -->
    <Sidebar v-model="drawer" />

    <!-- App Bar -->
    <AppBar @toggle-drawer="toggleDrawer" />

    <!-- Main Content -->
    <v-main style="min-height: calc(100vh - 120px);">
      <v-container fluid class="pa-6">
        <router-view />
      </v-container>
    </v-main>

    <!-- Footer -->
    <Footer />
  </v-layout>
</template>

<script>
import { ref } from 'vue'
import AppBar from '@/components/Layout/AppBar.vue'
import Sidebar from '@/components/Layout/Sidebar.vue'
import Footer from '@/components/Layout/Footer.vue'

export default {
  name: 'DefaultLayout',
  components: {
    AppBar,
    Sidebar,
    Footer
  },
  setup() {
    const drawer = ref(true)

    const toggleDrawer = () => {
      drawer.value = !drawer.value
    }

    return {
      drawer,
      toggleDrawer
    }
  }
}
</script>
