<!-- src/components/Common/NotificationContainer.vue -->
<template>
  <div class="notification-container">
    <v-snackbar
      v-for="notification in notifications"
      :key="notification.id"
      v-model="notification.show"
      :timeout="notification.timeout"
      :color="getNotificationColor(notification.type)"
      location="top right"
      class="notification-item"
    >
      <div class="d-flex align-center">
        <v-icon :icon="getNotificationIcon(notification.type)" class="mr-2"></v-icon>
        <span>{{ notification.message }}</span>
      </div>

      <template v-slot:actions>
        <v-btn
          icon
          variant="text"
          @click="closeNotification(notification.id)"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()

const notifications = computed(() => appStore.notifications)

const getNotificationColor = (type) => {
  const colors = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info'
  }
  return colors[type] || 'info'
}

const getNotificationIcon = (type) => {
  const icons = {
    success: 'mdi-check-circle',
    error: 'mdi-alert-circle',
    warning: 'mdi-alert',
    info: 'mdi-information'
  }
  return icons[type] || 'mdi-information'
}

const closeNotification = (id) => {
  appStore.removeNotification(id)
}
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 1000;
}

.notification-item {
  margin-bottom: 10px;
}
</style>
