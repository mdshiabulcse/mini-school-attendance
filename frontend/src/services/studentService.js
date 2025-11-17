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
    if (studentData instanceof FormData) {
      studentData.append('_method', 'PUT');
      return api.post(`/students/${id}`, studentData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    }
    const formData = new FormData();
    Object.keys(studentData).forEach(key => {
      if (studentData[key] !== null && studentData[key] !== undefined) {
        let value = studentData[key];
        if (typeof value === 'boolean') {
          value = value.toString();
        }
        formData.append(key, value);
      }
    });

    // Add method spoofing for PUT
    formData.append('_method', 'PUT');

    console.log('✅ Final FormData prepared for student ID:', id);
    // Log FormData contents for debugging
    for (let [key, value] of formData.entries()) {
      console.log(`   ${key}: ${value} (type: ${typeof value})`);
    }

    return api.post(`/students/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
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
