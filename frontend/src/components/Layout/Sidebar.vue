<!-- src/components/Layout/Sidebar.vue -->
<template>
  <v-navigation-drawer
    v-model="drawer"
    color="grey-lighten-4"
    app
  >
    <!-- Header -->
    <div class="pa-4 text-center border-b">
      <v-avatar size="64" color="primary" class="mb-3">
        <v-icon color="white" size="28">mdi-school</v-icon>
      </v-avatar>
      <div class="text-h6 primary--text font-weight-bold">SMIS</div>
      <div class="text-caption grey--text">School Management</div>
    </div>

    <!-- Navigation Menu -->
    <v-list density="comfortable" class="pa-3">
      <v-list-item
        to="/"
        :active="isActive('/')"
        color="primary"
        class="mb-1 rounded"
        variant="flat"
      >
        <template v-slot:prepend>
          <v-icon>mdi-view-dashboard</v-icon>
        </template>
        <v-list-item-title>Dashboard</v-list-item-title>
      </v-list-item>

      <v-list-item
        v-if="isTeacher || isAdmin"
        to="/students"
        :active="isActive('/students')"
        color="primary"
        class="mb-1 rounded"
        variant="flat"
      >
        <template v-slot:prepend>
          <v-icon>mdi-account-multiple</v-icon>
        </template>
        <v-list-item-title>Students</v-list-item-title>
      </v-list-item>

      <v-list-item
        v-if="isTeacher || isAdmin"
        to="/attendance"
        :active="isActive('/attendance')"
        color="primary"
        class="mb-1 rounded"
        variant="flat"
      >
        <template v-slot:prepend>
          <v-icon>mdi-clipboard-check</v-icon>
        </template>
        <v-list-item-title>Attendance</v-list-item-title>
      </v-list-item>

      <v-list-item
        v-if="isTeacher || isAdmin"
        to="/reports"
        :active="isActive('/reports')"
        color="primary"
        class="mb-1 rounded"
        variant="flat"
      >
        <template v-slot:prepend>
          <v-icon>mdi-chart-bar</v-icon>
        </template>
        <v-list-item-title>Reports</v-list-item-title>
      </v-list-item>

      <v-list-item
        v-if="isAdmin"
        to="/admin/users"
        :active="isActive('/admin/users')"
        color="primary"
        class="mb-1 rounded"
        variant="flat"
      >
        <template v-slot:prepend>
          <v-icon>mdi-account-cog</v-icon>
        </template>
        <v-list-item-title>User Management</v-list-item-title>
      </v-list-item>
    </v-list>

    <!-- Bottom Section -->
    <template v-slot:append>
      <div class="pa-4">
        <v-divider class="mb-3"></v-divider>
        <div class="text-center">
          <div class="text-caption grey--text mb-1">SMIS v1.0.0</div>
          <v-chip size="small" color="success" variant="flat">
            <v-icon start size="16">mdi-circle</v-icon>
            Online
          </v-chip>
        </div>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()
const drawer = ref(true)

const isAdmin = computed(() => authStore.isAdmin)
const isTeacher = computed(() => authStore.isTeacher)

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const handleToggleDrawer = () => {
  drawer.value = !drawer.value
}

onMounted(() => {
  window.addEventListener('toggle-drawer', handleToggleDrawer)
})

onUnmounted(() => {
  window.removeEventListener('toggle-drawer', handleToggleDrawer)
})
</script>

<style scoped>
.border-b {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.v-list-item--active {
  background-color: rgba(25, 118, 210, 0.08);
  border-left: 3px solid #1976d2;
}
</style>
