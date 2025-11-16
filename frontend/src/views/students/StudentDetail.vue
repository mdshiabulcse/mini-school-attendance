<template>
  <v-container fluid class="page-container" v-if="student">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <v-btn
              icon
              @click="$router.back()"
              class="mr-4"
              variant="text"
            >
              <v-icon>mdi-arrow-left</v-icon>
            </v-btn>
            <div>
              <h1 class="text-h3 font-weight-bold primary--text mb-2">{{ student.name }}</h1>
              <p class="text-subtitle-1 grey--text">Student Details & Attendance History</p>
            </div>
          </div>
          <div class="header-actions">
            <v-btn
              color="primary"
              @click="editStudent"
              prepend-icon="mdi-pencil"
              class="mr-2"
            >
              Edit Student
            </v-btn>
            <v-btn
              variant="outlined"
              @click="goBack"
              prepend-icon="mdi-arrow-left"
            >
              Back to List
            </v-btn>
          </div>
        </div>
      </v-col>
    </v-row>

    <v-row>
      <!-- Student Information -->
      <v-col cols="12" lg="4">
        <v-card class="elevation-3 rounded-lg mb-6">
          <v-card-title class="card-title">
            <span class="text-h6 font-weight-bold">Student Information</span>
          </v-card-title>
          <v-card-text class="pa-6">
            <div class="text-center mb-6">
              <v-avatar size="120" :color="student.photo ? 'transparent' : 'primary'" class="mb-4">
                <v-img v-if="student.photo" :src="student.photo" alt="Student Photo"></v-img>
                <span v-else class="white--text text-h5">
                  {{ getUserInitials(student.name) }}
                </span>
              </v-avatar>
              <div class="text-h5 font-weight-bold">{{ student.name }}</div>
              <div class="text-body-1 text-medium-emphasis">{{ student.email }}</div>
              <v-chip color="primary" class="mt-2">
                {{ student.class }}-{{ student.section }}
              </v-chip>
            </div>

            <v-divider class="my-4"></v-divider>

            <v-list density="comfortable">
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-identifier</v-icon>
                </template>
                <v-list-item-title>Student ID</v-list-item-title>
                <v-list-item-subtitle>{{ student.student_id }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-sort-numeric</v-icon>
                </template>
                <v-list-item-title>Roll Number</v-list-item-title>
                <v-list-item-subtitle>{{ student.roll_number || 'N/A' }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-cake</v-icon>
                </template>
                <v-list-item-title>Date of Birth</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(student.date_of_birth) || 'N/A' }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-gender-male-female</v-icon>
                </template>
                <v-list-item-title>Gender</v-list-item-title>
                <v-list-item-subtitle class="text-capitalize">{{ student.gender }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-account</v-icon>
                </template>
                <v-list-item-title>Parent Name</v-list-item-title>
                <v-list-item-subtitle>{{ student.parent_name }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-email</v-icon>
                </template>
                <v-list-item-title>Parent Email</v-list-item-title>
                <v-list-item-subtitle>{{ student.parent_email || 'N/A' }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-phone</v-icon>
                </template>
                <v-list-item-title>Parent Phone</v-list-item-title>
                <v-list-item-subtitle>{{ student.parent_phone }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-map-marker</v-icon>
                </template>
                <v-list-item-title>Address</v-list-item-title>
                <v-list-item-subtitle>{{ student.address || 'N/A' }}</v-list-item-subtitle>
              </v-list-item>

              <v-list-item>
                <template v-slot:prepend>
                  <v-icon :color="student.is_active ? 'success' : 'error'">
                    {{ student.is_active ? 'mdi-check-circle' : 'mdi-close-circle' }}
                  </v-icon>
                </template>
                <v-list-item-title>Status</v-list-item-title>
                <v-list-item-subtitle>
                  <v-chip :color="student.is_active ? 'success' : 'error'" size="small" variant="flat">
                    {{ student.is_active ? 'Active' : 'Inactive' }}
                  </v-chip>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Attendance History -->
      <v-col cols="12" lg="8">
        <v-card class="elevation-3 rounded-lg">
          <v-card-title class="card-title d-flex justify-space-between align-center">
            <span class="text-h6 font-weight-bold">Attendance History</span>
            <div class="d-flex align-center">
              <v-text-field
                v-model="attendanceFilters.month"
                label="Select Month"
                type="month"
                variant="outlined"
                density="compact"
                hide-details
                style="max-width: 200px;"
                class="mr-4"
                @update:model-value="loadAttendance"
              ></v-text-field>
              <v-btn
                icon
                @click="loadAttendance"
                :loading="attendanceLoading"
                variant="text"
              >
                <v-icon>mdi-refresh</v-icon>
              </v-btn>
            </div>
          </v-card-title>

          <v-card-text class="pa-0">
            <v-data-table
              :headers="attendanceHeaders"
              :items="attendanceHistory"
              :loading="attendanceLoading"
              class="elevation-0"
            >
              <template v-slot:item.date="{ item }">
                <span class="font-weight-medium">{{ formatDate(item.date) }}</span>
              </template>

              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="getAttendanceColor(item.status)"
                  size="small"
                  variant="flat"
                >
                  <v-icon start size="16">{{ getAttendanceIcon(item.status) }}</v-icon>
                  {{ item.status }}
                </v-chip>
              </template>

              <template v-slot:item.note="{ item }">
                <span class="text-caption text-medium-emphasis">{{ item.note || 'No remarks' }}</span>
              </template>

              <template v-slot:item.recorded_by="{ item }">
                <span class="text-caption">{{ item.recorded_by?.name || 'System' }}</span>
              </template>

              <template v-slot:no-data>
                <div class="text-center py-8">
                  <v-icon size="64" class="mb-2 text-grey-lighten-2">mdi-clipboard-check</v-icon>
                  <div class="text-h6 text-grey mb-2">No attendance records</div>
                  <div class="text-body-1 text-medium-emphasis">
                    No attendance records found for the selected period
                  </div>
                </div>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>

        <!-- Attendance Summary -->
        <v-row class="mt-6">
          <v-col cols="12" sm="6" md="3">
            <v-card class="stat-card elevation-3 rounded-lg stat-card--green">
              <v-card-text class="pa-4 text-center">
                <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.present || 0 }}</div>
                <div class="text-subtitle-2 font-weight-medium">Present Days</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="stat-card elevation-3 rounded-lg stat-card--error">
              <v-card-text class="pa-4 text-center">
                <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.absent || 0 }}</div>
                <div class="text-subtitle-2 font-weight-medium">Absent Days</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="stat-card elevation-3 rounded-lg stat-card--warning">
              <v-card-text class="pa-4 text-center">
                <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.late || 0 }}</div>
                <div class="text-subtitle-2 font-weight-medium">Late Days</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card class="stat-card elevation-3 rounded-lg stat-card--primary">
              <v-card-text class="pa-4 text-center">
                <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.total || 0 }}</div>
                <div class="text-subtitle-2 font-weight-medium">Total Days</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Edit Dialog -->
    <StudentForm
      v-model="showEditDialog"
      :student="student"
      @submit="handleStudentUpdate"
    />
  </v-container>

  <!-- Loading State -->
  <v-container v-else>
    <v-row justify="center">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
        <div class="text-h6 mt-4">Loading student details...</div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNotification } from '@/composables/useNotification'
import StudentForm from '@/components/Dialogs/StudentForm.vue'
import { studentService } from '@/services/studentService'

const route = useRoute()
const router = useRouter()
const { showSuccess, showError } = useNotification()

// Reactive data
const student = ref(null)
const attendanceHistory = ref([])
const attendanceLoading = ref(false)
const showEditDialog = ref(false)
const updateLoading = ref(false)

// Filters
const attendanceFilters = ref({
  month: new Date().toISOString().slice(0, 7) // Current month
})

// Headers for attendance table
const attendanceHeaders = [
  { title: 'Date', key: 'date', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Remarks', key: 'note', sortable: false },
  { title: 'Recorded By', key: 'recorded_by', sortable: true }
]

// Computed properties
const attendanceSummary = computed(() => {
  const present = attendanceHistory.value.filter(a => a.status === 'present').length
  const absent = attendanceHistory.value.filter(a => a.status === 'absent').length
  const late = attendanceHistory.value.filter(a => a.status === 'late').length
  const halfDay = attendanceHistory.value.filter(a => a.status === 'half_day').length
  const total = attendanceHistory.value.length

  return {
    present,
    absent,
    late,
    halfDay,
    total
  }
})

// Methods
const loadStudent = async () => {
  try {
    const studentId = route.params.id
    const response = await studentService.getStudent(studentId)
    student.value = response.data.data
  } catch (error) {
    console.error('Failed to load student:', error)
    showError('Failed to load student details')
    router.back()
  }
}

const loadAttendance = async () => {
  if (!student.value) return

  attendanceLoading.value = true
  try {
    const params = {}
    if (attendanceFilters.value.month) {
      params.month = attendanceFilters.value.month
    }

    const response = await studentService.getStudentAttendance(student.value.id, params)

    if (response.data.success) {
      attendanceHistory.value = response.data.data.data || []
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to load attendance:', error)
    showError('Failed to load attendance history')
  } finally {
    attendanceLoading.value = false
  }
}

const editStudent = () => {
  showEditDialog.value = true
}

const handleStudentUpdate = async (studentData) => {
  updateLoading.value = true
  try {
    // studentData is FormData object from StudentForm component
    const response = await studentService.updateStudent(student.value.id, studentData)

    if (response.data.success) {
      showSuccess('Student updated successfully')
      showEditDialog.value = false
      await loadStudent() // Reload student data
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to update student:', error)
    showError(error.message || 'Failed to update student')
  } finally {
    updateLoading.value = false
  }
}

const goBack = () => {
  router.back()
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

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadStudent()
  loadAttendance()
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

.header-content {
  display: flex;
  align-items: flex-start;
}

.header-content h1 {
  background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.card-title {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  padding: 20px 24px;
}

/* Statistics Cards */
.stat-card {
  transition: all 0.3s ease;
  overflow: hidden;
  position: relative;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.stat-card--green::before { background: linear-gradient(90deg, #4CAF50, #2E7D32); }
.stat-card--error::before { background: linear-gradient(90deg, #F44336, #D32F2F); }
.stat-card--warning::before { background: linear-gradient(90deg, #FF9800, #F57C00); }
.stat-card--primary::before { background: linear-gradient(90deg, #2196F3, #0D47A1); }

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
</style>
