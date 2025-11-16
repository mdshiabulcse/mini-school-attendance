import { ref } from 'vue'

export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  const callApi = async (apiCall, ...args) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiCall(...args)
      return response
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    callApi
  }
}
