
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const notifications = ref([])

  const addNotification = (notification) => {
    const id = Date.now() + Math.random()
    const newNotification = {
      id,
      show: true,
      ...notification
    }

    notifications.value.push(newNotification)

    // Auto remove after timeout
    if (notification.timeout) {
      setTimeout(() => {
        removeNotification(id)
      }, notification.timeout)
    }
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(notification => notification.id === id)
    if (index !== -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearNotifications = () => {
    notifications.value = []
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    clearNotifications
  }
})
