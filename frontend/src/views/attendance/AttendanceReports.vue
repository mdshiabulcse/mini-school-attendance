<!-- src/views/attendance/AttendanceReports.vue -->
<template>
  <v-container fluid class="page-container">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <h1 class="text-h3 font-weight-bold primary--text mb-2">Attendance Reports</h1>
            <p class="text-subtitle-1 grey--text">View and analyze attendance data</p>
          </div>
          <div class="header-actions">
            <v-btn
              color="primary"
              @click="exportReport"
              :loading="exporting"
              prepend-icon="mdi-download"
              class="action-btn"
            >
              Export Report
            </v-btn>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Filters Section -->
    <v-card class="elevation-3 rounded-lg mb-8">
      <v-card-title class="card-title">
        <span class="text-h6 font-weight-bold">Report Filters</span>
      </v-card-title>
      <v-card-text class="pa-6">
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="filters.month"
              :items="availableMonths"
              label="Select Month"
              variant="outlined"
              @update:model-value="generateReport"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="filters.class"
              :items="classOptions"
              label="Select Class"
              variant="outlined"
              @update:model-value="generateReport"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-btn
              color="primary"
              @click="generateReport"
              :loading="loading"
              block
              class="mt-4"
            >
              Generate Report
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Summary Statistics -->
    <v-row class="mb-8">
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--blue">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-account-group</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ reportSummary.total_students || 0 }}</div>
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
            <div class="text-h4 font-weight-bold mb-1">{{ reportSummary.average_attendance || 0 }}%</div>
            <div class="text-subtitle-2 font-weight-medium">Average Attendance</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--primary">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-trophy</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ reportSummary.excellent_performance || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Excellent (90%+)</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--orange">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-alert</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ reportSummary.poor_performance || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Needs Improvement</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Detailed Report Table -->
    <v-card class="elevation-3 rounded-lg data-card">
      <v-card-title class="card-title d-flex justify-space-between align-center">
        <div>
          <span class="text-h6 font-weight-bold">Monthly Attendance Report</span>
          <v-chip small color="primary" class="ml-2" text-color="white">
            {{ reportData.length }} Records
          </v-chip>
        </div>
        <div class="d-flex align-center">
          <v-text-field
            v-model="search"
            label="Search students..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            style="max-width: 300px;"
            class="mr-4"
          ></v-text-field>
        </div>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="reportData"
        :search="search"
        :loading="loading"
        class="elevation-1 rounded-lg"
      >
        <template v-slot:item.name="{ item }">
          <div class="d-flex align-center">
            <v-avatar size="36" color="primary" class="mr-3">
              <span class="white--text text-caption">
                {{ getUserInitials(item.name) }}
              </span>
            </v-avatar>
            <div>
              <div class="font-weight-medium">{{ item.name }}</div>
              <div class="text-caption text-medium-emphasis">
                Roll: {{ item.roll_number }}
              </div>
            </div>
          </div>
        </template>

        <template v-slot:item.attendance_percentage="{ item }">
          <div class="d-flex align-center">
            <v-progress-circular
              :model-value="item.attendance_percentage"
              :color="getPercentageColor(item.attendance_percentage)"
              size="40"
              width="3"
              class="mr-3"
            >
              {{ Math.round(item.attendance_percentage) }}%
            </v-progress-circular>
            <v-chip
              :color="getPercentageColor(item.attendance_percentage)"
              size="small"
              variant="flat"
            >
              {{ getPerformanceText(item.attendance_percentage) }}
            </v-chip>
          </div>
        </template>

        <template v-slot:item.present_days="{ item }">
          <span class="text-success font-weight-medium">{{ item.present_days }}</span>
        </template>

        <template v-slot:item.absent_days="{ item }">
          <span class="text-error font-weight-medium">{{ item.absent_days }}</span>
        </template>

        <template v-slot:item.late_days="{ item }">
          <span class="text-warning font-weight-medium">{{ item.late_days }}</span>
        </template>

        <template v-slot:item.half_days="{ item }">
          <span class="text-orange font-weight-medium">{{ item.half_days }}</span>
        </template>

        <template v-slot:no-data>
          <div class="text-center py-8">
            <v-icon size="64" class="mb-2 text-grey-lighten-2">mdi-chart-bar</v-icon>
            <div class="text-h6 text-grey mb-2">No report data</div>
            <div class="text-body-1 text-medium-emphasis mb-4">
              Select a month and generate report to view attendance data
            </div>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Class Performance Section -->
    <v-row class="mt-8">
      <v-col cols="12" lg="6">
        <v-card class="elevation-3 rounded-lg">
          <v-card-title class="card-title">
            <span class="text-h6 font-weight-bold">Class Performance</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-list>
              <v-list-item
                v-for="classStat in classPerformance"
                :key="classStat.class"
                class="performance-item"
              >
                <template v-slot:prepend>
                  <v-avatar color="primary" size="48" class="mr-4">
                    <span class="white--text text-body-1">C{{ classStat.class }}</span>
                  </v-avatar>
                </template>
                <v-list-item-title class="font-weight-medium">
                  Class {{ classStat.class }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ classStat.total_students }} Students • Avg: {{ classStat.average_attendance }}%
                </v-list-item-subtitle>
                <template v-slot:append>
                  <v-progress-circular
                    :model-value="classStat.average_attendance"
                    :color="getPercentageColor(classStat.average_attendance)"
                    size="50"
                    width="4"
                  >
                    {{ Math.round(classStat.average_attendance) }}%
                  </v-progress-circular>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" lg="6">
        <v-card class="elevation-3 rounded-lg">
          <v-card-title class="card-title">
            <span class="text-h6 font-weight-bold">Performance Distribution</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <div class="performance-stats">
              <div class="performance-stat" v-for="stat in performanceStats" :key="stat.label">
                <div class="stat-bar-container">
                  <div
                    class="stat-bar"
                    :style="{ width: stat.percentage + '%' }"
                    :class="'stat-bar--' + stat.color"
                  ></div>
                </div>
                <div class="stat-info">
                  <div class="stat-label">{{ stat.label }}</div>
                  <div class="stat-value">{{ stat.count }} students</div>
                  <div class="stat-percentage">{{ stat.percentage }}%</div>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotification } from '@/composables/useNotification'
import { attendanceService } from '@/services/attendanceService'
import { reportService } from '@/services/reportService'

const { showSuccess, showError } = useNotification()

// Reactive data
const filters = ref({
  month: new Date().toISOString().slice(0, 7), // Current month in YYYY-MM format
  class: ''
})
const reportData = ref([])
const availableMonths = ref([])
const classPerformance = ref([])
const loading = ref(false)
const exporting = ref(false)
const search = ref('')

// Constants
const classOptions = [
  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
  'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'
]

// Headers for data table
const headers = [
  { title: 'Student', key: 'name', sortable: true },
  { title: 'Class', key: 'class', sortable: true },
  { title: 'Section', key: 'section', sortable: true },
  { title: 'Roll No', key: 'roll_number', sortable: true },
  { title: 'Attendance %', key: 'attendance_percentage', sortable: true },
  { title: 'Present', key: 'present_days', sortable: true },
  { title: 'Absent', key: 'absent_days', sortable: true },
  { title: 'Late', key: 'late_days', sortable: true },
  { title: 'Half Day', key: 'half_days', sortable: true },
  { title: 'Total Days', key: 'total_days', sortable: true }
]

// Computed properties
const reportSummary = computed(() => {
  if (reportData.value.length === 0) return {}

  const totalStudents = reportData.value.length
  const avgAttendance = reportData.value.reduce((sum, item) => sum + item.attendance_percentage, 0) / totalStudents
  const excellent = reportData.value.filter(item => item.attendance_percentage >= 90).length
  const good = reportData.value.filter(item => item.attendance_percentage >= 75 && item.attendance_percentage < 90).length
  const average = reportData.value.filter(item => item.attendance_percentage >= 60 && item.attendance_percentage < 75).length
  const poor = reportData.value.filter(item => item.attendance_percentage < 60).length

  return {
    total_students: totalStudents,
    average_attendance: Math.round(avgAttendance),
    excellent_performance: excellent,
    good_performance: good,
    average_performance: average,
    poor_performance: poor
  }
})

const performanceStats = computed(() => {
  const total = reportData.value.length
  if (total === 0) return []

  return [
    {
      label: 'Excellent (90-100%)',
      count: reportSummary.value.excellent_performance,
      percentage: Math.round((reportSummary.value.excellent_performance / total) * 100),
      color: 'success'
    },
    {
      label: 'Good (75-89%)',
      count: reportSummary.value.good_performance,
      percentage: Math.round((reportSummary.value.good_performance / total) * 100),
      color: 'primary'
    },
    {
      label: 'Average (60-74%)',
      count: reportSummary.value.average_performance,
      percentage: Math.round((reportSummary.value.average_performance / total) * 100),
      color: 'warning'
    },
    {
      label: 'Needs Improvement (<60%)',
      count: reportSummary.value.poor_performance,
      percentage: Math.round((reportSummary.value.poor_performance / total) * 100),
      color: 'error'
    }
  ]
})

// Methods
const loadAvailableMonths = async () => {
  try {
    const response = await reportService.getReportMonths()
    if (response.data.success) {
      availableMonths.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load available months:', error)
    // Fallback to recent months
    availableMonths.value = generateRecentMonths()
  }
}

const generateRecentMonths = () => {
  const months = []
  const today = new Date()
  for (let i = 0; i < 12; i++) {
    const date = new Date(today.getFullYear(), today.getMonth() - i, 1)
    months.push(date.toISOString().slice(0, 7))
  }
  return months
}

const generateReport = async () => {
  if (!filters.value.month) {
    showError('Please select a month')
    return
  }

  loading.value = true
  try {
    const params = {
      month: filters.value.month,
      class: filters.value.class || undefined
    }

    const response = await attendanceService.getMonthlyReport(params)

    if (response.data.success) {
      reportData.value = response.data.data
      await loadClassPerformance()
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to generate report:', error)
    showError('Failed to generate report')
  } finally {
    loading.value = false
  }
}

const loadClassPerformance = async () => {
  // This would typically come from a separate API endpoint
  // For now, we'll calculate it from the report data
  if (reportData.value.length === 0) {
    classPerformance.value = []
    return
  }

  const classStats = {}
  reportData.value.forEach(student => {
    if (!classStats[student.class]) {
      classStats[student.class] = {
        class: student.class,
        total_students: 0,
        total_attendance: 0
      }
    }
    classStats[student.class].total_students++
    classStats[student.class].total_attendance += student.attendance_percentage
  })

  classPerformance.value = Object.values(classStats).map(stat => ({
    ...stat,
    average_attendance: Math.round(stat.total_attendance / stat.total_students)
  }))
}

const exportReport = async () => {
  if (reportData.value.length === 0) {
    showError('No data to export')
    return
  }

  exporting.value = true
  try {
    const params = {
      month: filters.value.month,
      class: filters.value.class || undefined,
      format: 'csv'
    }

    const response = await reportService.downloadMonthlyReport(params)

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `attendance-report-${filters.value.month}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    showSuccess('Report exported successfully')
  } catch (error) {
    console.error('Failed to export report:', error)
    showError('Failed to export report')
  } finally {
    exporting.value = false
  }
}

const getPercentageColor = (percentage) => {
  if (percentage >= 90) return 'success'
  if (percentage >= 75) return 'primary'
  if (percentage >= 60) return 'warning'
  return 'error'
}

const getPerformanceText = (percentage) => {
  if (percentage >= 90) return 'Excellent'
  if (percentage >= 75) return 'Good'
  if (percentage >= 60) return 'Average'
  return 'Needs Improvement'
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
  loadAvailableMonths()
  generateReport()
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

/* Performance Stats */
.performance-stats {
  padding: 8px 0;
}

.performance-stat {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  padding: 12px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.02);
}

.stat-bar-container {
  flex: 1;
  height: 8px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  margin-right: 16px;
  overflow: hidden;
}

.stat-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.stat-bar--success { background: #4CAF50; }
.stat-bar--primary { background: #2196F3; }
.stat-bar--warning { background: #FF9800; }
.stat-bar--error { background: #F44336; }

.stat-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex: 2;
}

.stat-label {
  font-weight: 500;
  flex: 2;
}

.stat-value {
  color: rgba(0, 0, 0, 0.6);
  flex: 1;
  text-align: center;
}

.stat-percentage {
  font-weight: 600;
  flex: 1;
  text-align: right;
}

.performance-item {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.performance-item:last-child {
  border-bottom: none;
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
  .performance-stat {
    flex-direction: column;
    align-items: stretch;
  }

  .stat-bar-container {
    margin-right: 0;
    margin-bottom: 8px;
  }

  .stat-info {
    flex-direction: column;
    align-items: flex-start;
  }

  .stat-value, .stat-percentage {
    text-align: left;
    margin-top: 4px;
  }
}
</style>
