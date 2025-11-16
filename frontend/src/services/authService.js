// src/services/authService.js
import api from './api'

export const authService = {
  async login(credentials) {
    const response = await api.post('/login', credentials)
    return response.data
  },

  async register(userData) {
    const response = await api.post('/register', userData)
    return response.data
  },

  async logout() {
    const response = await api.post('/logout')
    return response.data
  },

  async checkAuth() {
    const response = await api.get('/check-auth')
    return response.data
  },

  async updateProfile(userData) {
    const response = await api.put('/profile', userData)
    return response.data
  }
}
