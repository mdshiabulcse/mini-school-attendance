<!-- src/components/Layout/AppBar.vue -->
<template>
  <v-app-bar color="primary" elevation="2">
    <!-- Left Section -->
    <v-app-bar-nav-icon
      variant="text"
      color="white"
      @click="toggleDrawer"
    ></v-app-bar-nav-icon>

    <v-app-bar-title class="white--text">
      <div class="d-flex align-center">
        <v-icon color="white" class="mr-2">mdi-school</v-icon>
        <span class="text-h6 font-weight-bold">SMIS</span>
      </div>
    </v-app-bar-title>

    <v-spacer></v-spacer>

    <!-- Right Section -->
    <div class="d-flex align-center">
      <!-- Notifications -->
      <v-btn icon color="white" class="mr-2">
        <v-badge color="error" dot>
          <v-icon>mdi-bell-outline</v-icon>
        </v-badge>
      </v-btn>

      <!-- User Menu -->
      <v-menu location="bottom end">
        <template v-slot:activator="{ props }">
          <v-btn variant="text" color="white" v-bind="props">
            <v-avatar size="32" color="white" class="mr-2">
              <span class="primary--text text-caption">{{ userInitials }}</span>
            </v-avatar>
            <span class="mr-1">{{ userName }}</span>
            <v-icon>mdi-chevron-down</v-icon>
          </v-btn>
        </template>

        <v-card width="250">
          <v-list>
            <v-list-item class="px-4 py-3">
              <template v-slot:prepend>
                <v-avatar color="primary" size="48">
                  <span class="white--text text-body-1">{{ userInitials }}</span>
                </v-avatar>
              </template>
              <v-list-item-title class="text-h6">{{ userName }}</v-list-item-title>
              <v-list-item-subtitle class="text-capitalize">{{ userRole }}</v-list-item-subtitle>
            </v-list-item>

            <v-divider></v-divider>

            <v-list-item @click="updateProfile" prepend-icon="mdi-account-cog">
              <v-list-item-title>Profile Settings</v-list-item-title>
            </v-list-item>

            <v-divider></v-divider>

            <v-list-item @click="logout" prepend-icon="mdi-logout" color="error">
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>
    </div>
  </v-app-bar>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const userName = computed(() => authStore.userName)
const userRole = computed(() => authStore.userRole)
const userInitials = computed(() => authStore.userInitials)

const toggleDrawer = () => {
  // This will be handled by the layout
  const event = new CustomEvent('toggle-drawer')
  window.dispatchEvent(event)
}

const logout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

const updateProfile = () => {
  console.log('Update profile')
}
</script>
