import api from './api'

export const studentService = {
  getStudents(params = {}) {
    return api.get('/students', { params })
  },

  getStudent(id) {
    return api.get(`/students/${id}`)
  },

  createStudent(studentData) {
    return api.post('/students', studentData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  updateStudent(id, studentData) {
    return api.put(`/students/${id}`, studentData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  deleteStudent(id) {
    return api.delete(`/students/${id}`)
  },

  getStudentAttendance(id, params = {}) {
    return api.get(`/students/${id}/attendance`, { params })
  },

  getStudentsByClassSection(className, section) {
    return api.get(`/students/class/${className}/section/${section}`)
  },

  bulkImport(formData) {
    return api.post('/students/bulk-import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}
