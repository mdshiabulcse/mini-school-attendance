<!-- frontend/src/views/Dashboard.vue -->
<template>
  <v-container fluid class="dashboard-container">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <div class="dashboard-header">
          <div class="header-content">
            <h1 class="text-h3 font-weight-bold primary--text mb-2">School Dashboard</h1>
            <p class="text-subtitle-1 grey--text">Welcome to School Management System</p>
          </div>
          <div class="header-actions">
            <v-btn
              color="primary"
              @click="refreshDashboard"
              :loading="isLoading"
              class="refresh-btn"
              depressed
            >
              <v-icon left>mdi-refresh</v-icon>
              Refresh Data
            </v-btn>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Statistics Cards -->
    <v-row class="mb-8">
      <v-col
        v-for="stat in statistics"
        :key="stat.title"
        cols="12"
        sm="6"
        md="4"
        lg="2"
      >
        <v-card
          class="stat-card elevation-3 rounded-lg"
          :class="`stat-card--${stat.color}`"
        >
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">{{ stat.icon }}</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ stat.value }}</div>
            <div class="text-subtitle-2 font-weight-medium">{{ stat.title }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts and Performance Section -->
    <v-row class="mb-8">
      <!-- Monthly Attendance Chart -->
      <v-col cols="12" lg="8">
        <v-card class="elevation-3 rounded-lg chart-card">
          <v-card-title class="chart-title d-flex justify-space-between align-center">
            <div>
              <span class="text-h6 font-weight-bold">Monthly Attendance Trend</span>
              <v-chip small color="primary" class="ml-2" text-color="white">
                Last 30 Days
              </v-chip>
            </div>
            <v-btn icon small>
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-4">
            <div class="chart-container">
              <AttendanceChart :chart-data="monthlyChart" :height="280" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Class Performance -->
      <v-col cols="12" lg="4">
        <v-card class="elevation-3 rounded-lg performance-card">
          <v-card-title class="d-flex justify-space-between align-center">
            <span class="text-h6 font-weight-bold">Class Performance</span>
            <v-btn icon small>
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-list class="performance-list" dense>
              <v-list-item
                v-for="classStat in classPerformance"
                :key="classStat.class"
                class="performance-item"
              >
                <v-list-item-avatar class="class-avatar">
                  <div class="class-badge">C{{ classStat.class }}</div>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium">
                    Class {{ classStat.class }}
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption">
                    {{ classStat.student_count }} Students
                  </v-list-item-subtitle>
                </v-list-item-content>
                <v-list-item-action>
                  <v-progress-circular
                    :value="classStat.attendance_rate"
                    :color="getPerformanceColor(classStat.attendance_rate)"
                    size="50"
                    width="4"
                    class="mr-2"
                  >
                    {{ classStat.attendance_rate }}%
                  </v-progress-circular>
                </v-list-item-action>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Activities and Quick Actions -->
    <v-row>
      <!-- Recent Activities -->
      <v-col cols="12" lg="8">
        <v-card class="elevation-3 rounded-lg activities-card">
          <v-card-title class="d-flex justify-space-between align-center">
            <span class="text-h6 font-weight-bold">Recent Activities</span>
            <v-btn text color="primary" to="/attendance" class="view-all-btn">
              View All
              <v-icon right>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-timeline align-top dense class="activities-timeline">
              <v-timeline-item
                v-for="activity in recentActivities"
                :key="activity.id"
                :color="activity.status_color"
                small
                fill-dot
              >
                <div class="activity-item">
                  <div class="d-flex justify-space-between align-start">
                    <div class="activity-content">
                      <div class="activity-header">
                        <strong>{{ activity.student_name }}</strong>
                        <span class="class-badge ml-2">Class {{ activity.student_class }}</span>
                      </div>
                      <div class="activity-details text-caption mt-1">
                        Marked <span :class="`status-${activity.status.toLowerCase()}`">{{ activity.status }}</span> • {{ activity.recorded_by }}
                      </div>
                    </div>
                    <div class="activity-time text-caption text-right">
                      {{ activity.time }}
                    </div>
                  </div>
                </div>
                </v-timeline-item>
            </v-timeline>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Quick Actions -->
      <v-col cols="12" lg="4">
        <v-card class="elevation-3 rounded-lg quick-actions-card">
          <v-card-title>
            <span class="text-h6 font-weight-bold">Quick Actions</span>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-list class="actions-list" dense>
              <v-list-item
                v-for="action in quickActions"
                :key="action.title"
                @click="action.click"
                class="action-item"
                ripple
              >
                <v-list-item-avatar class="action-avatar">
                  <v-icon :color="action.color" size="24">{{ action.icon }}</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium">{{ action.title }}</v-list-item-title>
                  <v-list-item-subtitle class="text-caption">{{ action.subtitle }}</v-list-item-subtitle>
                </v-list-item-content>
                <v-list-item-action>
                  <v-icon color="grey lighten-1">mdi-chevron-right</v-icon>
                </v-list-item-action>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDashboardStore } from '@/stores/dashboard.js'
import { useAuthStore } from '@/stores/auth.js'
import AttendanceChart from '@/components/Charts/AttendanceChart.vue'

const router = useRouter()
const dashboardStore = useDashboardStore()
const authStore = useAuthStore()

const stats = computed(() => dashboardStore.stats)
const recentActivities = computed(() => dashboardStore.recentActivities)
const monthlyChart = computed(() => dashboardStore.monthlyChart)
const classPerformance = computed(() => dashboardStore.classPerformance)
const isLoading = computed(() => dashboardStore.isLoading)

const statistics = computed(() => [
  {
    title: 'Total Students',
    value: stats.value.total_students || 0,
    icon: 'mdi-account-group',
    color: 'blue'
  },
  {
    title: 'Total Teachers',
    value: stats.value.total_teachers || 0,
    icon: 'mdi-teach',
    color: 'green'
  },
  {
    title: 'Present Today',
    value: stats.value.present_today || 0,
    icon: 'mdi-check-circle',
    color: 'success'
  },
  {
    title: 'Absent Today',
    value: stats.value.absent_today || 0,
    icon: 'mdi-close-circle',
    color: 'error'
  },
  {
    title: 'Monthly Rate',
    value: `${stats.value.monthly_attendance_rate || 0}%`,
    icon: 'mdi-chart-line',
    color: 'primary'
  },
  {
    title: 'Not Marked',
    value: stats.value.not_marked_today || 0,
    icon: 'mdi-clock-outline',
    color: 'orange'
  }
])

const quickActions = computed(() => {
  const actions = [
    {
      title: 'Take Attendance',
      subtitle: 'Record today\'s attendance',
      icon: 'mdi-clipboard-check',
      color: 'success',
      click: () => router.push('/attendance')
    },
    {
      title: 'View Students',
      subtitle: 'Browse student directory',
      icon: 'mdi-account-multiple',
      color: 'primary',
      click: () => router.push('/students')
    }
  ]

  if (authStore.isAdmin) {
    actions.push({
      title: 'Manage Users',
      subtitle: 'Admin user management',
      icon: 'mdi-account-cog',
      color: 'warning',
      click: () => router.push('/admin/users')
    })
  }

  return actions
})

const getPerformanceColor = (rate) => {
  if (rate >= 90) return 'success'
  if (rate >= 75) return 'primary'
  if (rate >= 60) return 'warning'
  return 'error'
}

const refreshDashboard = async () => {
  await dashboardStore.refreshDashboard()
}

onMounted(() => {
  dashboardStore.loadDashboardData()
})
</script>

<style scoped>
.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
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

.refresh-btn {
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
  background: linear-gradient(90deg, var(--v-primary-base), var(--v-secondary-base));
}

.stat-card--blue::before { background: linear-gradient(90deg, #2196F3, #1976D2); }
.stat-card--green::before { background: linear-gradient(90deg, #4CAF50, #2E7D32); }
.stat-card--success::before { background: linear-gradient(90deg, #66BB6A, #388E3C); }
.stat-card--error::before { background: linear-gradient(90deg, #F44336, #D32F2F); }
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

/* Chart Styling */
.chart-card {
  height: 100%;
}

.chart-title {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  padding: 20px 24px;
}

.chart-container {
  height: 320px;
  position: relative;
}

/* Performance Card */
.performance-card {
  height: 100%;
}

.performance-list {
  padding: 8px 0;
}

.performance-item {
  padding: 16px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  transition: background-color 0.2s ease;
}

.performance-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.performance-item:last-child {
  border-bottom: none;
}

.class-avatar {
  min-width: 50px;
}

.class-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 8px;
  background: rgba(0, 0, 0, 0.06);
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Activities Card */
.activities-card {
  height: 100%;
}

.activities-timeline {
  padding: 16px 0;
}

.activity-item {
  padding: 8px 0;
}

.activity-header {
  display: flex;
  align-items: center;
}

.activity-details .status-present {
  color: #4CAF50;
  font-weight: 600;
}

.activity-details .status-absent {
  color: #F44336;
  font-weight: 600;
}

.activity-details .status-late {
  color: #FF9800;
  font-weight: 600;
}

.activity-time {
  color: rgba(0, 0, 0, 0.6);
  white-space: nowrap;
}

.view-all-btn {
  text-transform: none;
  font-weight: 600;
}

/* Quick Actions Card */
.quick-actions-card {
  height: 100%;
}

.actions-list {
  padding: 8px 0;
}

.action-item {
  padding: 16px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
  cursor: pointer;
}

.action-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
  transform: translateX(4px);
}

.action-item:last-child {
  border-bottom: none;
}

.action-avatar {
  min-width: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.04);
  border-radius: 10px;
  height: 40px;
  width: 40px;
}

/* Responsive adjustments */
@media (max-width: 1263px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    margin-top: 16px;
    width: 100%;
  }

  .refresh-btn {
    width: 100%;
  }
}

@media (max-width: 959px) {
  .chart-container {
    height: 280px;
  }
}
</style>
