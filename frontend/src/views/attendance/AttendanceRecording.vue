<!-- src/views/attendance/AttendanceRecording.vue -->
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
            ></v-text-field>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Attendance Form -->
    <v-card v-if="students.length > 0" class="elevation-3 rounded-lg">
      <v-card-title class="card-title d-flex justify-space-between align-center">
        <div>
          <span class="text-h6 font-weight-bold">Student Attendance</span>
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
          <tr v-for="student in students" :key="student.id" class="attendance-row">
            <td class="roll-number">
              <v-chip size="small" variant="flat" color="primary">
                {{ student.roll_number }}
              </v-chip>
            </td>
            <td class="student-info">
              <div class="d-flex align-center">
                <v-avatar size="36" color="primary" class="mr-3">
                    <span class="white--text text-caption">
                      {{ getUserInitials(student.name) }}
                    </span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ student.name }}</div>
                  <div class="text-caption text-medium-emphasis">
                    Class {{ student.class }}-{{ student.section }}
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
                @focus="expandRemarks(student.id)"
              ></v-text-field>
            </td>
          </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>

    <!-- Empty State -->
    <v-card v-else class="elevation-3 rounded-lg">
      <v-card-text class="text-center py-12">
        <v-icon size="64" class="mb-4 text-grey-lighten-2">mdi-account-multiple</v-icon>
        <div class="text-h6 text-grey mb-2">No students found</div>
        <div class="text-body-1 text-medium-emphasis mb-4">
          Please select a class and section to load students
        </div>
      </v-card-text>
    </v-card>

    <!-- Summary Dialog -->
    <v-dialog v-model="showSummaryDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h6">Attendance Summary</v-card-title>
        <v-card-text>
          <v-list>
            <v-list-item>
              <v-list-item-title>Total Students</v-list-item-title>
              <v-list-item-subtitle>{{ students.length }}</v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Present</v-list-item-title>
              <v-list-item-subtitle class="text-success">
                {{ getStatusCount('present') }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Absent</v-list-item-title>
              <v-list-item-subtitle class="text-error">
                {{ getStatusCount('absent') }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Late</v-list-item-title>
              <v-list-item-subtitle class="text-warning">
                {{ getStatusCount('late') }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Half Day</v-list-item-title>
              <v-list-item-subtitle class="text-orange">
                {{ getStatusCount('half_day') }}
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
import { ref, computed, onMounted } from 'vue'
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

// Constants
const today = ref(new Date().toISOString().split('T')[0])
const classOptions = [
  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
  'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'
]
const sectionOptions = ['A', 'B', 'C', 'D']

// Computed
const attendanceSummary = computed(() => {
  const present = getStatusCount('present')
  const absent = getStatusCount('absent')
  const late = getStatusCount('late')
  const halfDay = getStatusCount('half_day')
  const total = students.value.length

  return {
    present,
    absent,
    late,
    halfDay,
    total,
    marked: present + absent + late + halfDay,
    pending: total - (present + absent + late + halfDay)
  }
})

// Methods
const onClassChange = () => {
  selectedSection.value = ''
  students.value = []
  attendance.value = {}
}

const loadStudents = async () => {
  if (!selectedClass.value || !selectedSection.value) return

  loadingStudents.value = true
  try {
    const response = await studentService.getStudentsByClassSection(
      selectedClass.value,
      selectedSection.value
    )

    if (response.data.success) {
      students.value = response.data.data
      initializeAttendanceData()
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to load students:', error)
    showError('Failed to load students')
  } finally {
    loadingStudents.value = false
  }
}

const initializeAttendanceData = async () => {
  // Check if attendance already exists for today
  try {
    const response = await attendanceService.getClassAttendance(
      selectedClass.value,
      selectedSection.value,
      { date: attendanceDate.value }
    )

    if (response.data.success && response.data.data.length > 0) {
      // Load existing attendance
      response.data.data.forEach(student => {
        if (student.attendances && student.attendances.length > 0) {
          const attendanceRecord = student.attendances[0]
          attendance.value[student.id] = {
            student_id: student.id,
            status: attendanceRecord.status,
            note: attendanceRecord.note || ''
          }
        } else {
          attendance.value[student.id] = {
            student_id: student.id,
            status: 'present',
            note: ''
          }
        }
      })
    } else {
      // Initialize with default values
      students.value.forEach(student => {
        attendance.value[student.id] = {
          student_id: student.id,
          status: 'present',
          note: ''
        }
      })
    }
  } catch (error) {
    // Initialize with default values if API fails
    students.value.forEach(student => {
      attendance.value[student.id] = {
        student_id: student.id,
        status: 'present',
        note: ''
      }
    })
  }
}

const markAllPresent = () => {
  Object.keys(attendance.value).forEach(studentId => {
    attendance.value[studentId].status = 'present'
  })
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

    const response = await attendanceService.recordBulk(attendanceData)

    if (response.data.success) {
      showSuccess('Attendance recorded successfully')
      showSummaryDialog.value = true
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to record attendance:', error)
    showError('Failed to record attendance')
  } finally {
    submitting.value = false
  }
}

const getStatusCount = (status) => {
  return Object.values(attendance.value).filter(item => item.status === status).length
}

const expandRemarks = (studentId) => {
  // You can add any special handling for remarks field focus
}

const refreshClassData = () => {
  if (selectedClass.value && selectedSection.value) {
    loadStudents()
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

// Lifecycle
onMounted(() => {
  // You can load initial data here if needed
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

.attendance-row:last-child {
  border-bottom: none;
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
  .attendance-table {
    display: block;
    overflow-x: auto;
  }

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
