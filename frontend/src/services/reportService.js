// src/services/reportService.js
import api from './api'

export const reportService = {
  downloadMonthlyReport(params = {}) {
    return api.get('/download-monthly', {
      params,
      responseType: 'blob'
    })
  },

  getReportMonths() {
    return api.get('/months')
  }
}
