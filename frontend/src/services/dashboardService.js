// src/services/dashboardService.js
import api from './api'

export const dashboardService = {
  async getStats() {
    const response = await api.get('/dashboard/stats')
    return response.data
  },

  async getRecentActivities(limit = 10) {
    const response = await api.get('/dashboard/recent-activities', {
      params: { limit }
    })
    return response.data
  },

  async getMonthlyChart(year = null, month = null) {
    const params = {}
    if (year) params.year = year
    if (month) params.month = month

    const response = await api.get('/dashboard/monthly-chart', { params })
    return response.data
  },

  async getClassPerformance() {
    const response = await api.get('/dashboard/class-performance')
    return response.data
  },

  async getUpcomingEvents() {
    const response = await api.get('/dashboard/upcoming-events')
    return response.data
  }
}
