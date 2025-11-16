// src/services/attendanceService.js
import api from './api'

export const attendanceService = {
  recordBulk(attendanceData) {
    return api.post('/attendance/bulk', attendanceData)
  },

  getTodayAttendance() {
    return api.get('/attendance/today')
  },

  getMonthlyReport(params = {}) {
    return api.get('/attendance/monthly-report', { params })
  },

  getClassAttendance(className, section, params = {}) {
    return api.get(`/attendance/class/${className}/section/${section}`, { params })
  },

  getStudentAttendance(studentId, params = {}) {
    return api.get(`/attendance/student/${studentId}`, { params })
  },

  getDailyReport(params = {}) {
    return api.get('/attendance/daily-report', { params })
  }
}
