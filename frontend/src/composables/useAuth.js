import { useAuthStore } from '@/stores/auth'

export function useAuth() {
  const authStore = useAuthStore()

  const hasPermission = (permission) => {
    if (!authStore.user) return false
    return authStore.user.permissions?.includes(permission)
  }

  const hasRole = (role) => {
    if (!authStore.user) return false
    return authStore.user.role === role
  }

  return {
    hasPermission,
    hasRole,
    user: authStore.user,
    isAuthenticated: authStore.isAuthenticated
  }
}
