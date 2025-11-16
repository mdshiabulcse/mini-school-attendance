<!-- src/views/admin/UserManagement.vue -->
<template>
  <v-container fluid class="page-container">
    <!-- Header Section -->
    <v-row class="mb-8">
      <v-col cols="12">
        <div class="page-header">
          <div class="header-content">
            <h1 class="text-h3 font-weight-bold primary--text mb-2">User Management</h1>
            <p class="text-subtitle-1 grey--text">Manage system users and permissions</p>
          </div>
          <div class="header-actions">
            <v-btn
              color="primary"
              @click="showAddDialog"
              prepend-icon="mdi-plus"
              class="action-btn"
              depressed
            >
              Add User
            </v-btn>
            <v-btn
              variant="outlined"
              @click="refreshUsers"
              :loading="loading"
              prepend-icon="mdi-refresh"
              class="ml-2"
            >
              Refresh
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
            <div class="text-h4 font-weight-bold mb-1">{{ systemStats.total_users || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Total Users</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--green">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-teach</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ systemStats.teacher_users || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Teachers</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--primary">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-shield-account</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ systemStats.admin_users || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Administrators</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="stat-card elevation-3 rounded-lg stat-card--orange">
          <v-card-text class="pa-4 text-center">
            <div class="stat-icon-container mb-3">
              <v-icon size="40" class="stat-icon">mdi-account-clock</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">{{ systemStats.active_users || 0 }}</div>
            <div class="text-subtitle-2 font-weight-medium">Active Users</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Users Table -->
    <v-card class="elevation-3 rounded-lg data-card">
      <v-card-title class="card-title d-flex justify-space-between align-center">
        <div>
          <span class="text-h6 font-weight-bold">System Users</span>
          <v-chip small color="primary" class="ml-2" text-color="white">
            {{ pagination.total }} Users
          </v-chip>
        </div>
        <div class="d-flex align-center">
          <v-text-field
            v-model="search"
            label="Search users..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            style="max-width: 300px;"
            class="mr-4"
            @input="handleSearch"
          ></v-text-field>
          <v-select
            v-model="filters.role"
            :items="roleOptions"
            label="Role"
            variant="outlined"
            density="compact"
            hide-details
            style="max-width: 150px;"
            class="mr-4"
            @update:model-value="loadUsers"
            clearable
          ></v-select>
        </div>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="users"
        :loading="loading"
        :items-per-page="pagination.per_page"
        :page="pagination.current_page"
        :items-length="pagination.total"
        @update:options="handleTableOptions"
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
              <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <template v-slot:item.role="{ item }">
          <v-chip
            :color="getRoleColor(item.role)"
            size="small"
            variant="flat"
          >
            <v-icon start size="16">{{ getRoleIcon(item.role) }}</v-icon>
            {{ item.role }}
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

        <template v-slot:item.last_login_at="{ item }">
          <span class="text-caption text-medium-emphasis">
            {{ formatDate(item.last_login_at) }}
          </span>
        </template>

        <template v-slot:item.created_at="{ item }">
          <span class="text-caption text-medium-emphasis">
            {{ formatDate(item.created_at) }}
          </span>
        </template>

        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn
              icon
              size="small"
              @click="editUser(item)"
              color="primary"
              variant="text"
              class="mr-1"
            >
              <v-icon size="18">mdi-pencil</v-icon>
              <v-tooltip activator="parent" location="top">Edit User</v-tooltip>
            </v-btn>
            <v-btn
              icon
              size="small"
              @click="toggleUserStatus(item)"
              :color="item.is_active ? 'warning' : 'success'"
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
              @click="deleteUser(item)"
              color="error"
              variant="text"
              :disabled="item.id === authStore.user?.id"
            >
              <v-icon size="18">mdi-delete</v-icon>
              <v-tooltip activator="parent" location="top">Delete User</v-tooltip>
            </v-btn>
          </div>
        </template>

        <template v-slot:no-data>
          <div class="text-center py-8">
            <v-icon size="64" class="mb-2 text-grey-lighten-2">mdi-account-multiple</v-icon>
            <div class="text-h6 text-grey mb-2">No users found</div>
            <v-btn color="primary" @click="showAddDialog">
              Add First User
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialogs -->
    <UserForm
      v-model="showDialog"
      :user="selectedUser"
      @submit="handleUserSubmit"
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
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotification } from '@/composables/useNotification'
import UserForm from '@/components/Dialogs/UserForm.vue'
import ConfirmDialog from '@/components/Dialogs/ConfirmDialog.vue'
import { adminService } from '@/services/adminService'

const authStore = useAuthStore()
const { showSuccess, showError } = useNotification()

// Reactive data
const search = ref('')
const loading = ref(false)
const actionLoading = ref(false)
const users = ref([])
const systemStats = ref({})
const showDialog = ref(false)
const showConfirmDialog = ref(false)
const selectedUser = ref(null)
const confirmAction = ref(null)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmText = ref('')
const confirmColor = ref('primary')

// Filters and pagination
const filters = ref({
  role: null,
  search: ''
})

const pagination = ref({
  current_page: 1,
  per_page: 10,
  total: 0
})

let searchTimeout = null

// Headers for data table
const headers = [
  { title: 'User', key: 'name', sortable: true },
  { title: 'Role', key: 'role', sortable: true },
  { title: 'Phone', key: 'phone', sortable: true },
  { title: 'Status', key: 'is_active', sortable: true },
  { title: 'Last Login', key: 'last_login_at', sortable: true },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' }
]

const roleOptions = [
  { title: 'Admin', value: 'admin' },
  { title: 'Teacher', value: 'teacher' },
  { title: 'Parent', value: 'parent' }
]

// Methods
const loadUsers = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: filters.value.search,
      role: filters.value.role
    }

    console.log('Loading users with params:', params) // Debug log

    const response = await adminService.getUsers(params)

    console.log('API Response:', response.data) // Debug log

    if (response.data.success) {
      // Extract users from the nested structure
      users.value = response.data.data.users.data
      pagination.value = {
        current_page: response.data.data.users.current_page,
        per_page: response.data.data.users.per_page,
        total: response.data.data.users.total
      }
      console.log('Users loaded:', users.value) // Debug log
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to load users:', error)
    showError('Failed to load users: ' + error.message)
  } finally {
    loading.value = false
  }
}

const loadSystemStats = async () => {
  try {
    const response = await adminService.getSystemStats()
    if (response.data.success) {
      systemStats.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load system stats:', error)
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filters.value.search = search.value
    loadUsers(1)
  }, 500)
}

const handleTableOptions = (options) => {
  pagination.value.per_page = options.itemsPerPage
  pagination.value.current_page = options.page
  loadUsers(options.page)
}

const refreshUsers = () => {
  loadUsers(pagination.value.current_page)
  loadSystemStats()
}

const showAddDialog = () => {
  selectedUser.value = null
  showDialog.value = true
}

const editUser = (user) => {
  selectedUser.value = { ...user }
  showDialog.value = true
}

const toggleUserStatus = (user) => {
  selectedUser.value = user
  confirmTitle.value = user.is_active ? 'Deactivate User' : 'Activate User'
  confirmMessage.value = `Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} ${user.name}?`
  confirmText.value = user.is_active ? 'Deactivate' : 'Activate'
  confirmColor.value = user.is_active ? 'warning' : 'success'
  confirmAction.value = confirmToggleStatus
  showConfirmDialog.value = true
}

const deleteUser = (user) => {
  selectedUser.value = user
  confirmTitle.value = 'Delete User'
  confirmMessage.value = `Are you sure you want to delete ${user.name}? This action cannot be undone.`
  confirmText.value = 'Delete'
  confirmColor.value = 'error'
  confirmAction.value = confirmDelete
  showConfirmDialog.value = true
}

const confirmToggleStatus = async () => {
  actionLoading.value = true
  try {
    const updateData = {
      is_active: !selectedUser.value.is_active
    }

    const response = await adminService.updateUser(selectedUser.value.id, updateData)

    if (response.data.success) {
      showSuccess(`User ${selectedUser.value.is_active ? 'deactivated' : 'activated'} successfully`)
      await loadUsers(pagination.value.current_page)
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to update user status:', error)
    showError('Failed to update user status')
  } finally {
    actionLoading.value = false
    showConfirmDialog.value = false
  }
}

const confirmDelete = async () => {
  actionLoading.value = true
  try {
    const response = await adminService.deleteUser(selectedUser.value.id)

    if (response.data.success) {
      showSuccess('User deleted successfully')
      await loadUsers(pagination.value.current_page)
      await loadSystemStats()
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to delete user:', error)
    showError(error.message || 'Failed to delete user')
  } finally {
    actionLoading.value = false
    showConfirmDialog.value = false
  }
}

const handleUserSubmit = async (userData) => {
  actionLoading.value = true
  try {
    let response

    if (selectedUser.value) {
      // Update existing user
      response = await adminService.updateUser(selectedUser.value.id, userData)
    } else {
      // Create new user
      response = await adminService.createUser(userData)
    }

    if (response.data.success) {
      showSuccess(`User ${selectedUser.value ? 'updated' : 'created'} successfully`)
      showDialog.value = false
      await loadUsers(pagination.value.current_page)
      await loadSystemStats()
    } else {
      throw new Error(response.data.message)
    }
  } catch (error) {
    console.error('Failed to save user:', error)
    showError(error.message || 'Failed to save user')
  } finally {
    actionLoading.value = false
  }
}

const getRoleColor = (role) => {
  const colors = {
    admin: 'primary',
    teacher: 'green',
    parent: 'blue'
  }
  return colors[role] || 'grey'
}

const getRoleIcon = (role) => {
  const icons = {
    admin: 'mdi-shield-account',
    teacher: 'mdi-teach',
    parent: 'mdi-account-child'
  }
  return icons[role] || 'mdi-account'
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
  if (!dateString) return 'Never'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadUsers()
  loadSystemStats()
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

  .card-title > div:last-child {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
