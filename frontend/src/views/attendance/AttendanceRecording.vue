
<template>
  <v-container fluid class="page-container">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <h1 class="text-h3 font-weight-bold primary--text mb-2">Record Attendance</h1>
            <p class="text-subtitle-1 grey--text">Mark attendance for students</p>
          </div>
          <div class="header-actions">
            <v-btn
              color="primary"
              @click="refreshClassData"
              :loading="loading"
              prepend-icon="mdi-refresh"
              class="action-btn"
            >
              Refresh
            </v-btn>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Class Selection -->
    <v-card class="elevation-3 rounded-lg mb-8">
      <v-card-title class="card-title">
        <span class="text-h6 font-weight-bold">Select Class & Section</span>
      </v-card-title>
      <v-card-text class="pa-6">
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="selectedClass"
              :items="classOptions"
              label="Select Class"
              variant="outlined"
              @update:model-value="onClassChange"
              :loading="loadingClasses"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="selectedSection"
              :items="sectionOptions"
              label="Select Section"
              variant="outlined"
              @update:model-value="loadStudents"
              :disabled="!selectedClass"
              :loading="loadingStudents"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="attendanceDate"
              label="Attendance Date"
              type="date"
              variant="outlined"
              :max="today"
              @update:model-value="onDateChange"
            ></v-text-field>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Quick Stats -->
    <v-row v-if="students.length > 0" class="mb-6">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--blue">
          <v-card-text class="pa-4 text-center">
            <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.total || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Total Students</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--green">
          <v-card-text class="pa-4 text-center">
            <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.present || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Present</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--orange">
          <v-card-text class="pa-4 text-center">
            <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.absent || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Absent</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--primary">
          <v-card-text class="pa-4 text-center">
            <div class="text-h4 font-weight-bold mb-1">{{ attendanceSummary.attendanceRate || 0 }}%</div>
            <div class="text-subtitle-2 font-weight-medium">Attendance Rate</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Attendance Form -->
    <v-card v-if="students.length > 0 && attendanceInitialized" class="elevation-3 rounded-lg">
      <v-card-title class="card-title d-flex justify-space-between align-center">
        <div>
          <span class="text-h6 font-weight-bold">Student Attendance - {{ selectedClass }}-{{ selectedSection }}</span>
          <v-chip small color="primary" class="ml-2" text-color="white">
            {{ students.length }} Students
          </v-chip>
        </div>
        <div class="d-flex align-center">
          <v-btn
            variant="outlined"
            @click="markAllPresent"
            class="mr-2"
            :disabled="submitting"
          >
            Mark All Present
          </v-btn>
          <v-btn
            color="primary"
            @click="submitAttendance"
            :loading="submitting"
            prepend-icon="mdi-content-save"
          >
            Save Attendance
          </v-btn>
        </div>
      </v-card-title>

      <v-card-text class="pa-0">
        <v-table class="attendance-table">
          <thead>
          <tr>
            <th class="text-left">Roll No</th>
            <th class="text-left">Student Name</th>
            <th class="text-center">Status</th>
            <th class="text-left">Remarks</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="student in sortedStudents" :key="student.id" class="attendance-row">
            <td class="roll-number">
              <v-chip size="small" variant="flat" color="primary">
                {{ student.roll_number || 'N/A' }}
              </v-chip>
            </td>
            <td class="student-info">
              <div class="d-flex align-center">
                <v-avatar size="36" :color="student.photo ? 'transparent' : 'primary'" class="mr-3">
                  <v-img v-if="student.photo" :src="getPhotoUrl(student.photo)" alt="Student Photo"></v-img>
                  <span v-else class="white--text text-caption">
                    {{ getUserInitials(student.name) }}
                  </span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ student.name }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ student.student_id }}
                  </div>
                </div>
              </div>
            </td>
            <td class="status-cell">
              <v-btn-toggle
                v-model="attendance[student.id].status"
                mandatory
                class="status-buttons"
                color="primary"
              >
                <v-btn
                  value="present"
                  size="small"
                  :variant="attendance[student.id].status === 'present' ? 'flat' : 'outlined'"
                  color="success"
                >
                  <v-icon start size="18">mdi-check</v-icon>
                  Present
                </v-btn>
                <v-btn
                  value="absent"
                  size="small"
                  :variant="attendance[student.id].status === 'absent' ? 'flat' : 'outlined'"
                  color="error"
                >
                  <v-icon start size="18">mdi-close</v-icon>
                  Absent
                </v-btn>
                <v-btn
                  value="late"
                  size="small"
                  :variant="attendance[student.id].status === 'late' ? 'flat' : 'outlined'"
                  color="warning"
                >
                  <v-icon start size="18">mdi-clock</v-icon>
                  Late
                </v-btn>
                <v-btn
                  value="half_day"
                  size="small"
                  :variant="attendance[student.id].status === 'half_day' ? 'flat' : 'outlined'"
                  color="orange"
                >
                  <v-icon start size="18">mdi-clock-outline</v-icon>
                  Half Day
                </v-btn>
              </v-btn-toggle>
            </td>
            <td class="remarks-cell">
              <v-text-field
                v-model="attendance[student.id].note"
                placeholder="Optional remarks..."
                variant="outlined"
                density="compact"
                hide-details
              ></v-text-field>
            </td>
          </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>

    <!-- Loading State -->
    <v-card v-else-if="loadingStudents" class="elevation-3 rounded-lg">
      <v-card-text class="text-center py-12">
        <v-progress-circular
          indeterminate
          color="primary"
          size="64"
          class="mb-4"
        ></v-progress-circular>
        <div class="text-h6 text-grey mb-2">Loading Students</div>
        <div class="text-body-1 text-medium-emphasis">
          Please wait while we load the student data...
        </div>
      </v-card-text>
    </v-card>

    <!-- Empty State -->
    <v-card v-else-if="selectedClass && selectedSection" class="elevation-3 rounded-lg">
      <v-card-text class="text-center py-12">
        <v-icon size="64" class="mb-4 text-grey-lighten-2">mdi-account-multiple</v-icon>
        <div class="text-h6 text-grey mb-2">No students found</div>
        <div class="text-body-1 text-medium-emphasis mb-4">
          No active students found in {{ selectedClass }}-{{ selectedSection }}
        </div>
      </v-card-text>
    </v-card>

    <!-- Initial State -->
    <v-card v-else class="elevation-3 rounded-lg">
      <v-card-text class="text-center py-12">
        <v-icon size="64" class="mb-4 text-grey-lighten-2">mdi-calendar-check</v-icon>
        <div class="text-h6 text-grey mb-2">Ready to Record Attendance</div>
        <div class="text-body-1 text-medium-emphasis mb-4">
          Please select a class and section to load students
        </div>
      </v-card-text>
    </v-card>

    <!-- Summary Dialog -->
    <v-dialog v-model="showSummaryDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h6">Attendance Recorded Successfully</v-card-title>
        <v-card-text>
          <v-list>
            <v-list-item>
              <v-list-item-title>Class</v-list-item-title>
              <v-list-item-subtitle>{{ selectedClass }}-{{ selectedSection }}</v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Date</v-list-item-title>
              <v-list-item-subtitle>{{ formatDate(attendanceDate) }}</v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Total Students</v-list-item-title>
              <v-list-item-subtitle>{{ attendanceSummary.total }}</v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Present</v-list-item-title>
              <v-list-item-subtitle class="text-success">
                {{ attendanceSummary.present }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Absent</v-list-item-title>
              <v-list-item-subtitle class="text-error">
                {{ attendanceSummary.absent }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Late</v-list-item-title>
              <v-list-item-subtitle class="text-warning">
                {{ attendanceSummary.late }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Half Day</v-list-item-title>
              <v-list-item-subtitle class="text-orange">
                {{ attendanceSummary.halfDay }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Attendance Rate</v-list-item-title>
              <v-list-item-subtitle class="text-primary font-weight-bold">
                {{ attendanceSummary.attendanceRate }}%
              </v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="showSummaryDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useNotification } from '@/composables/useNotification'
import { attendanceService } from '@/services/attendanceService'
import { studentService } from '@/services/studentService'

const { showSuccess, showError } = useNotification()

// Reactive data
const selectedClass = ref('')
const selectedSection = ref('')
const attendanceDate = ref(new Date().toISOString().split('T')[0])
const students = ref([])
const attendance = ref({})
const loading = ref(false)
const loadingClasses = ref(false)
const loadingStudents = ref(false)
const submitting = ref(false)
const showSummaryDialog = ref(false)
const attendanceInitialized = ref(false)

// Constants
const today = ref(new Date().toISOString().split('T')[0])
const classOptions = [
  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
  'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'
]
const sectionOptions = ['A', 'B', 'C', 'D']

// Computed
const sortedStudents = computed(() => {
  return [...students.value].sort((a, b) => {
    // Sort by roll number, handle non-numeric roll numbers
    const rollA = parseInt(a.roll_number) || 0
    const rollB = parseInt(b.roll_number) || 0
    return rollA - rollB
  })
})

const attendanceSummary = computed(() => {
  const present = getStatusCount('present')
  const absent = getStatusCount('absent')
  const late = getStatusCount('late')
  const halfDay = getStatusCount('half_day')
  const total = students.value.length

  // Calculate attendance rate (Present + 0.5*HalfDay) / Total
  const effectivePresent = present + (halfDay * 0.5)
  const attendanceRate = total > 0 ? Math.round((effectivePresent / total) * 100) : 0

  return {
    present,
    absent,
    late,
    halfDay,
    total,
    marked: present + absent + late + halfDay,
    pending: total - (present + absent + late + halfDay),
    attendanceRate
  }
})

// Methods
const onClassChange = () => {
  selectedSection.value = ''
  students.value = []
  attendance.value = {}
  attendanceInitialized.value = false
}

const onDateChange = () => {
  if (selectedClass.value && selectedSection.value) {
    loadStudents()
  }
}

const loadStudents = async () => {
  if (!selectedClass.value || !selectedSection.value) return

  loadingStudents.value = true
  students.value = [] // Clear previous students
  attendance.value = {} // Clear previous attendance
  attendanceInitialized.value = false

  try {
    console.log('🔄 Loading students for:', selectedClass.value, selectedSection.value)

    const response = await studentService.getStudentsByClassSection(
      selectedClass.value,
      selectedSection.value
    )

    console.log('👥 Students API Response:', response)

    // Handle API response structure
    if (response.data && Array.isArray(response.data)) {
      students.value = response.data
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      students.value = response.data.data
    } else if (response.data && response.data.success && Array.isArray(response.data.data)) {
      students.value = response.data.data
    } else {
      students.value = []
    }

    console.log(`✅ Loaded ${students.value.length} students`)

    // Wait for next tick to ensure DOM is ready
    await nextTick()
    await initializeAttendanceData()

  } catch (error) {
    console.error('❌ Failed to load students:', error)
    showError('Failed to load students: ' + error.message)
    students.value = []
  } finally {
    loadingStudents.value = false
  }
}

const initializeAttendanceData = async () => {
  try {
    console.log('📊 Initializing attendance data for date:', attendanceDate.value)

    const response = await attendanceService.getClassAttendance(
      selectedClass.value,
      selectedSection.value,
      { date: attendanceDate.value }
    )

    console.log('📊 Attendance API Response:', response)

    let existingAttendance = []

    // Handle different API response structures for attendance
    if (response.data && Array.isArray(response.data)) {
      existingAttendance = response.data
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      existingAttendance = response.data.data
    } else if (response.data && response.data.success && Array.isArray(response.data.data)) {
      existingAttendance = response.data.data
    }

    // Create a fresh attendance object to avoid reactivity issues
    const newAttendance = {}

    if (existingAttendance && existingAttendance.length > 0) {
      // Load existing attendance from API response
      existingAttendance.forEach(student => {
        if (student.attendances && student.attendances.length > 0) {
          const attendanceRecord = student.attendances[0]
          newAttendance[student.id] = {
            student_id: student.id,
            status: attendanceRecord.status,
            note: attendanceRecord.note || ''
          }
        } else {
          // No attendance record exists, set default
          newAttendance[student.id] = {
            student_id: student.id,
            status: 'present',
            note: ''
          }
        }
      })
      console.log('✅ Loaded existing attendance records')
    } else {
      // No existing attendance found, initialize all as present
      console.log('🆕 No existing attendance found, initializing defaults')
      students.value.forEach(student => {
        newAttendance[student.id] = {
          student_id: student.id,
          status: 'present',
          note: ''
        }
      })
    }

    // Replace the entire attendance object to avoid reactivity issues
    attendance.value = newAttendance
    attendanceInitialized.value = true

    console.log('✅ Attendance data initialized successfully')

  } catch (error) {
    console.error('❌ Failed to initialize attendance data:', error)
    // Initialize with default values if API fails
    const newAttendance = {}
    students.value.forEach(student => {
      newAttendance[student.id] = {
        student_id: student.id,
        status: 'present',
        note: ''
      }
    })
    attendance.value = newAttendance
    attendanceInitialized.value = true
    console.log('🔄 Initialized default attendance due to error')
  }
}

const markAllPresent = () => {
  const newAttendance = { ...attendance.value }
  Object.keys(newAttendance).forEach(studentId => {
    newAttendance[studentId].status = 'present'
  })
  attendance.value = newAttendance
  console.log('✅ Marked all students as present')
}

const submitAttendance = async () => {
  if (!selectedClass.value || !selectedSection.value) {
    showError('Please select class and section')
    return
  }

  if (Object.keys(attendance.value).length === 0) {
    showError('No attendance data to save')
    return
  }

  submitting.value = true
  try {
    const attendanceData = {
      date: attendanceDate.value,
      class: selectedClass.value,
      section: selectedSection.value,
      attendances: Object.values(attendance.value)
    }

    console.log('📤 Submitting attendance data:', attendanceData)

    const response = await attendanceService.recordBulk(attendanceData)

    console.log('✅ Attendance submission response:', response)

    // Handle different success response structures
    const isSuccess = response.data?.success ||
      response.data?.status === 'success' ||
      response.status === 200

    if (isSuccess) {
      showSuccess('Attendance recorded successfully')
      showSummaryDialog.value = true
    } else {
      throw new Error(response.data?.message || 'Failed to record attendance')
    }
  } catch (error) {
    console.error('❌ Failed to record attendance:', error)

    if (error.response && error.response.data) {
      showError(error.response.data.message || 'Failed to record attendance')
    } else {
      showError(error.message || 'Failed to record attendance')
    }
  } finally {
    submitting.value = false
  }
}

const getStatusCount = (status) => {
  return Object.values(attendance.value).filter(item => item.status === status).length
}

const refreshClassData = () => {
  if (selectedClass.value && selectedSection.value) {
    loadStudents()
  } else {
    showError('Please select class and section first')
  }
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

const getPhotoUrl = (photoPath) => {
  if (!photoPath) return null
  // If it's already a full URL, return as is
  if (photoPath.startsWith('http')) return photoPath
  // Otherwise, construct the full URL
  return `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/storage/${photoPath}`
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  console.log('🎯 Attendance Recording component mounted')
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

.stat-card--blue::before { background: linear-gradient(90deg, #2196F3, #1976D2); }
.stat-card--green::before { background: linear-gradient(90deg, #4CAF50, #2E7D32); }
.stat-card--primary::before { background: linear-gradient(90deg, #2196F3, #0D47A1); }
.stat-card--orange::before { background: linear-gradient(90deg, #FF9800, #F57C00); }

.card-title {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  padding: 20px 24px;
}

.attendance-table {
  width: 100%;
}

.attendance-row {
  transition: background-color 0.2s ease;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.attendance-row:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.roll-number {
  width: 100px;
  padding: 16px;
}

.student-info {
  padding: 16px;
  min-width: 250px;
}

.status-cell {
  padding: 16px;
  width: 350px;
}

.remarks-cell {
  padding: 16px;
  min-width: 200px;
}

.status-buttons {
  flex-wrap: nowrap;
}

.status-buttons .v-btn {
  min-width: 80px;
  font-size: 0.75rem;
}

@media (max-width: 959px) {
  .status-buttons {
    flex-direction: column;
    gap: 4px;
  }

  .status-buttons .v-btn {
    min-width: 60px;
    font-size: 0.7rem;
  }
}
</style>
