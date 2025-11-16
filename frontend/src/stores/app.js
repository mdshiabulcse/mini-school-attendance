import { defineStore } from 'pinia'

export const useAppStore = defineStore('app', {
  state: () => ({
    loading: false,
    sidebar: true,
    notifications: [],
    theme: 'light'
  }),

  getters: {
    hasNotifications: (state) => state.notifications.length > 0
  },

  actions: {
    setLoading(loading) {
      this.loading = loading
    },

    toggleSidebar() {
      this.sidebar = !this.sidebar
    },

    addNotification(notification) {
      this.notifications.push({
        id: Date.now(),
        type: 'info',
        timeout: 5000,
        ...notification
      })
    },

    removeNotification(id) {
      this.notifications = this.notifications.filter(n => n.id !== id)
    },

    clearNotifications() {
      this.notifications = []
    },

    toggleTheme() {
      this.theme = this.theme === 'light' ? 'dark' : 'light'
    }
  },

  persist: true
})
