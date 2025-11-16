// src/stores/dashboard.js
import { defineStore } from 'pinia'
import { dashboardService } from '@/services/dashboardService'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: {},
    recentActivities: [],
    monthlyChart: [],
    classPerformance: [],
    upcomingEvents: [],
    isLoading: false
  }),

  getters: {
    statistics: (state) => [
      {
        title: 'Total Students',
        value: state.stats.total_students || 0,
        icon: 'mdi-account-group',
        color: 'blue',
        trend: '+5%'
      },
      {
        title: 'Present Today',
        value: state.stats.present_today || 0,
        icon: 'mdi-check-circle',
        color: 'green',
        trend: '+2%'
      },
      {
        title: 'Absent Today',
        value: state.stats.absent_today || 0,
        icon: 'mdi-close-circle',
        color: 'red',
        trend: '-1%'
      },
      {
        title: 'Attendance Rate',
        value: `${state.stats.attendance_rate_today || 0}%`,
        icon: 'mdi-chart-line',
        color: 'primary',
        trend: '+3%'
      }
    ]
  },

  actions: {
    async loadDashboardData() {
      this.isLoading = true
      try {
        const [stats, activities, chart, performance, events] = await Promise.all([
          dashboardService.getStats(),
          dashboardService.getRecentActivities(),
          dashboardService.getMonthlyChart(),
          dashboardService.getClassPerformance(),
          dashboardService.getUpcomingEvents()
        ])

        this.stats = stats.data
        this.recentActivities = activities.data
        this.monthlyChart = chart.data
        this.classPerformance = performance.data
        this.upcomingEvents = events.data
      } catch (error) {
        console.error('Failed to load dashboard data:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async refreshDashboard() {
      await this.loadDashboardData()
    }
  }
})
