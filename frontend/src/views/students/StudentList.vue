<template>
  <v-container fluid class="page-container">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <h1 class="text-h3 font-weight-bold primary--text mb-2">Student Management</h1>
            <p class="text-subtitle-1 grey--text">Manage student records and information</p>
          </div>
          <div class="header-actions">
            <v-btn
              color="primary"
              @click="showAddDialog"
              prepend-icon="mdi-plus"
              class="action-btn"
            >
              Add Student
            </v-btn>
            <v-btn
              variant="outlined"
              @click="openImportDialog"
              prepend-icon="mdi-upload"
              class="ml-2"
            >
              Import
            </v-btn>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Statistics Cards -->
    <v-row class="mb-8">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--blue">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-account-group</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ studentStats.total || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Total Students</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--green">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-check-circle</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ studentStats.active || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Active Students</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--primary">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-google-classroom</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ studentStats.classes || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Classes</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--orange">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-account-clock</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ studentStats.newThisMonth || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">New This Month</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Filters and Search -->
    <v-card class="elevation-3 rounded-lg mb-8">
      <v-card-text class="pa-6">
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="filters.search"
              label="Search students..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              @input="handleSearch"
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filters.class"
              :items="classOptions"
              label="Class"
              variant="outlined"
              @update:model-value="loadStudents"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filters.section"
              :items="sectionOptions"
              label="Section"
              variant="outlined"
              @update:model-value="loadStudents"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="filters.is_active"
              :items="statusOptions"
              label="Status"
              variant="outlined"
              @update:model-value="loadStudents"
              clearable
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Students Table -->
    <v-card class="elevation-3 rounded-lg data-card">
      <v-card-title class="card-title d-flex justify-space-between align-center">
        <div>
          <span class="text-h6 font-weight-bold">Students List</span>
          <v-chip small color="primary" class="ml-2" text-color="white">
            {{ pagination.total }} Students
          </v-chip>
        </div>
        <div class="d-flex align-center">
          <v-btn
            icon
            @click="loadStudents"
            :loading="loading"
            variant="text"
          >
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </div>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="students"
        :loading="loading"
        :items-per-page="pagination.per_page"
        :page="pagination.current_page"
        :items-length="pagination.total"
        @update:options="handleTableOptions"
        class="elevation-1 rounded-lg"
      >
        <template v-slot:item.photo="{ item }">
          <v-avatar size="40" :color="item.photo ? 'transparent' : 'primary'">
            <v-img v-if="item.photo" :src="item.photo" alt="Student Photo"></v-img>
            <span v-else class="white--text text-caption">
              {{ getUserInitials(item.name) }}
            </span>
          </v-avatar>
        </template>

        <template v-slot:item.name="{ item }">
          <div class="d-flex align-center">
            <div>
              <div class="font-weight-medium">{{ item.name }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.email || 'No email' }}</div>
            </div>
          </div>
        </template>

        <template v-slot:item.class_section="{ item }">
          <v-chip size="small" variant="flat" color="primary">
            {{ item.class }}-{{ item.section }}
          </v-chip>
        </template>

        <template v-slot:item.is_active="{ item }">
          <v-chip
            :color="item.is_active ? 'success' : 'error'"
            size="small"
            variant="flat"
          >
            <v-icon start size="16">
              {{ item.is_active ? 'mdi-check-circle' : 'mdi-close-circle' }}
            </v-icon>
            {{ item.is_active ? 'Active' : 'Inactive' }}
          </v-chip>
        </template>

        <template v-slot:item.today_attendance="{ item }">
          <v-chip
            v-if="item.today_attendance"
            :color="getAttendanceColor(item.today_attendance.status)"
            size="small"
            variant="flat"
          >
            <v-icon start size="16">{{ getAttendanceIcon(item.today_attendance.status) }}</v-icon>
            {{ item.today_attendance.status }}
          </v-chip>
          <span v-else class="text-caption text-medium-emphasis">Not Marked</span>
        </template>

        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn
              icon
              size="small"
              @click="viewStudent(item)"
              color="primary"
              variant="text"
              class="mr-1"
            >
              <v-icon size="18">mdi-eye</v-icon>
              <v-tooltip activator="parent" location="top">View Details</v-tooltip>
            </v-btn>
            <v-btn
              icon
              size="small"
              @click="editStudent(item)"
              color="warning"
              variant="text"
              class="mr-1"
            >
              <v-icon size="18">mdi-pencil</v-icon>
              <v-tooltip activator="parent" location="top">Edit Student</v-tooltip>
            </v-btn>
            <v-btn
              icon
              size="small"
              @click="toggleStudentStatus(item)"
              :color="item.is_active ? 'error' : 'success'"
              variant="text"
              class="mr-1"
            >
              <v-icon size="18">
                {{ item.is_active ? 'mdi-account-off' : 'mdi-account-check' }}
              </v-icon>
              <v-tooltip activator="parent" location="top">
                {{ item.is_active ? 'Deactivate' : 'Activate' }}
              </v-tooltip>
            </v-btn>
            <v-btn
              icon
              size="small"
              @click="deleteStudent(item)"
              color="error"
              variant="text"
            >
              <v-icon size="18">mdi-delete</v-icon>
              <v-tooltip activator="parent" location="top">Delete Student</v-tooltip>
            </v-btn>
          </div>
        </template>

        <template v-slot:no-data>
          <div class="text-center py-8">
            <v-icon size="64" class="mb-2 text-grey-lighten-2">mdi-account-multiple</v-icon>
            <div class="text-h6 text-grey mb-2">No students found</div>
            <div class="text-body-1 text-medium-emphasis mb-4">
              {{ filters.search || filters.class || filters.section ? 'Try adjusting your filters' : 'Add your first student to get started' }}
            </div>
            <v-btn color="primary" @click="showAddDialog">
              Add First Student
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialogs -->
    <StudentForm
      v-model="showDialog"
      :student="selectedStudent"
      @submit="handleStudentSubmit"
    />

    <ConfirmDialog
      v-model="showConfirmDialog"
      :title="confirmTitle"
      :message="confirmMessage"
      :confirm-text="confirmText"
      :confirm-color="confirmColor"
      :loading="actionLoading"
      @confirm="confirmAction"
    />

    <!-- Import Dialog -->
    <v-dialog v-model="showImportDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h6">Import Students</v-card-title>
        <v-card-text>
          <v-file-input
            v-model="importFile"
            label="Select CSV or Excel file"
            accept=".csv,.xlsx,.xls"
            variant="outlined"
            prepend-icon="mdi-file"
            class="mb-4"
          ></v-file-input>
          <div class="text-caption text-medium-emphasis">
            Supported formats: CSV, Excel (.xlsx, .xls)
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="closeImportDialog">Cancel</v-btn>
          <v-btn
            color="primary"
            @click="handleImport"
            :loading="importing"
            :disabled="!importFile"
          >
            Import
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotification } from '@/composables/useNotification'
import StudentForm from '@/components/Dialogs/StudentForm.vue'
import ConfirmDialog from '@/components/Dialogs/ConfirmDialog.vue'
import { studentService } from '@/services/studentService'

const router = useRouter()
const { showSuccess, showError } = useNotification()

// Reactive data
const students = ref([])
const loading = ref(false)
const actionLoading = ref(false)
const importing = ref(false)
const showDialog = ref(false)
const showConfirmDialog = ref(false)
const showImportDialog = ref(false)
const selectedStudent = ref(null)
const importFile = ref(null)
const confirmAction = ref(null)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmText = ref('')
const confirmColor = ref('primary')

// Filters and pagination
const filters = ref({
  search: '',
  class: '',
  section: '',
  is_active: ''
})

const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0
})

let searchTimeout = null

// Constants
const classOptions = [
  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
  'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'
]
const sectionOptions = ['A', 'B', 'C', 'D']
const statusOptions = [
  { title: 'Active', value: 'true' },
  { title: 'Inactive', value: 'false' }
]

// Headers for data table
const headers = [
  { title: 'Photo', key: 'photo', sortable: false, width: '80px' },
  { title: 'Student ID', key: 'student_id', sortable: true },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Class & Section', key: 'class_section', sortable: true },
  { title: 'Roll No', key: 'roll_number', sortable: true },
  { title: 'Parent', key: 'parent_name', sortable: true },
  { title: 'Phone', key: 'parent_phone', sortable: true },
  { title: 'Status', key: 'is_active', sortable: true },
  { title: 'Today Attendance', key: 'today_attendance', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: '200px' }
]

// Computed properties
const studentStats = computed(() => {
  const total = pagination.value.total || 0
  const active = students.value.filter(s => s.is_active).length
  const classes = [...new Set(students.value.map(s => s.class))].length
  const newThisMonth = students.value.filter(s => {
    if (!s.created_at) return false
    const created = new Date(s.created_at)
    const now = new Date()
    return created.getMonth() === now.getMonth() && created.getFullYear() === now.getFullYear()
  }).length

  return { total, active, classes, newThisMonth }
})

// Methods
const loadStudents = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    }

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })

    const response = await studentService.getStudents(params)

    if (response.data) {
      students.value = response.data.data || []
      pagination.value = {
        current_page: response.data.current_page || 1,
        per_page: response.data.per_page || 15,
        total: response.data.total || 0
      }
    }
  } catch (error) {
    console.error('Failed to load students:', error)
    showError('Failed to load students')
    students.value = []
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadStudents(1)
  }, 500)
}

const handleTableOptions = (options) => {
  pagination.value.per_page = options.itemsPerPage
  pagination.value.current_page = options.page
  loadStudents(options.page)
}

const showAddDialog = () => {
  selectedStudent.value = null
  showDialog.value = true
}

const viewStudent = (student) => {
  router.push(`/students/${student.id}`)
}

const editStudent = (student) => {
  selectedStudent.value = { ...student }
  showDialog.value = true
}

const toggleStudentStatus = (student) => {
  selectedStudent.value = student
  confirmTitle.value = student.is_active ? 'Deactivate Student' : 'Activate Student'
  confirmMessage.value = `Are you sure you want to ${student.is_active ? 'deactivate' : 'activate'} ${student.name}?`
  confirmText.value = student.is_active ? 'Deactivate' : 'Activate'
  confirmColor.value = student.is_active ? 'warning' : 'success'
  confirmAction.value = confirmToggleStatus
  showConfirmDialog.value = true
}

const deleteStudent = (student) => {
  selectedStudent.value = student
  confirmTitle.value = 'Delete Student'
  confirmMessage.value = `Are you sure you want to delete ${student.name}? This action cannot be undone.`
  confirmText.value = 'Delete'
  confirmColor.value = 'error'
  confirmAction.value = confirmDelete
  showConfirmDialog.value = true
}

const confirmToggleStatus = async () => {
  actionLoading.value = true
  try {
    const updateData = {
      is_active: !selectedStudent.value.is_active
    }

    const response = await studentService.updateStudent(selectedStudent.value.id, updateData)

    if (response.data.success) {
      showSuccess(`Student ${selectedStudent.value.is_active ? 'deactivated' : 'activated'} successfully`)
      await loadStudents(pagination.value.current_page)
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to update student status:', error)
    showError('Failed to update student status')
  } finally {
    actionLoading.value = false
    showConfirmDialog.value = false
  }
}

const confirmDelete = async () => {
  actionLoading.value = true
  try {
    const response = await studentService.deleteStudent(selectedStudent.value.id)

    if (response.data.success) {
      showSuccess('Student deleted successfully')
      await loadStudents(pagination.value.current_page)
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to delete student:', error)
    showError(error.message || 'Failed to delete student')
  } finally {
    actionLoading.value = false
    showConfirmDialog.value = false
  }
}

const handleStudentSubmit = async (studentData) => {
  actionLoading.value = true
  try {
    let response

    if (selectedStudent.value) {
      // Update existing student
      response = await studentService.updateStudent(selectedStudent.value.id, studentData)
    } else {
      // Create new student
      response = await studentService.createStudent(studentData)
    }

    if (response.data.success) {
      showSuccess(`Student ${selectedStudent.value ? 'updated' : 'created'} successfully`)
      showDialog.value = false
      await loadStudents(pagination.value.current_page)
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to save student:', error)
    showError(error.message || 'Failed to save student')
  } finally {
    actionLoading.value = false
  }
}

const openImportDialog = () => {
  importFile.value = null
  showImportDialog.value = true
}

const closeImportDialog = () => {
  showImportDialog.value = false
  importFile.value = null
}

const handleImport = async () => {
  if (!importFile.value) return

  importing.value = true
  try {
    const formData = new FormData()
    formData.append('file', importFile.value)

    const response = await studentService.bulkImport(formData)

    if (response.data.success) {
      showSuccess('Students imported successfully')
      closeImportDialog()
      await loadStudents(pagination.value.current_page)
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to import students:', error)
    showError(error.message || 'Failed to import students')
  } finally {
    importing.value = false
  }
}

const getAttendanceColor = (status) => {
  const colors = {
    present: 'success',
    absent: 'error',
    late: 'warning',
    half_day: 'orange'
  }
  return colors[status] || 'grey'
}

const getAttendanceIcon = (status) => {
  const icons = {
    present: 'mdi-check',
    absent: 'mdi-close',
    late: 'mdi-clock',
    half_day: 'mdi-clock-outline'
  }
  return icons[status] || 'mdi-help'
}

const getUserInitials = (name) => {
  if (!name) return 'U'
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

// Lifecycle
onMounted(() => {
  loadStudents()
})
</script>

<style scoped>
.page-container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.header-content h1 {
  background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.action-btn {
  border-radius: 8px;
  text-transform: none;
  font-weight: 600;
}

/* Statistics Cards */
.stat-card {
  transition: all 0.3s ease;
  overflow: hidden;
  position: relative;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.stat-card--blue::before { background: linear-gradient(90deg, #2196F3, #1976D2); }
.stat-card--green::before { background: linear-gradient(90deg, #4CAF50, #2E7D32); }
.stat-card--primary::before { background: linear-gradient(90deg, #2196F3, #0D47A1); }
.stat-card--orange::before { background: linear-gradient(90deg, #FF9800, #F57C00); }

.stat-icon-container {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: rgba(var(--v-primary-base), 0.1);
}

.stat-icon {
  color: rgba(var(--v-primary-base), 0.8);
}

/* Data Table */
.data-card {
  overflow: hidden;
}

.card-title {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  padding: 20px 24px;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
}

/* Responsive adjustments */
@media (max-width: 1263px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    margin-top: 16px;
    width: 100%;
  }
}

@media (max-width: 959px) {
  .card-title {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
}
</style>
