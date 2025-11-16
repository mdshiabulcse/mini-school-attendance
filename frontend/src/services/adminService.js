// src/services/adminService.js
import api from './api'

export const adminService = {
  getUsers(params = {}) {
    return api.get('/admin/users', { params })
  },

  createUser(userData) {
    return api.post('/admin/users', userData)
  },

  updateUser(id, userData) {
    return api.put(`/admin/users/${id}`, userData)
  },

  deleteUser(id) {
    return api.delete(`/admin/users/${id}`)
  },

  getSystemStats() {
    return api.get('/admin/system-stats')
  }
}
