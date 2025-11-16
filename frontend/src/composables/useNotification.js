
import { useAppStore } from '@/stores/app'

export function useNotification() {
  const appStore = useAppStore()

  const showSuccess = (message) => {
    appStore.addNotification({
      type: 'success',
      message,
      timeout: 3000
    })
  }

  const showError = (message) => {
    appStore.addNotification({
      type: 'error',
      message,
      timeout: 5000
    })
  }

  const showWarning = (message) => {
    appStore.addNotification({
      type: 'warning',
      message,
      timeout: 4000
    })
  }

  const showInfo = (message) => {
    appStore.addNotification({
      type: 'info',
      message,
      timeout: 3000
    })
  }

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo
  }
}
