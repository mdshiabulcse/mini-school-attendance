<template>
  <v-container class="fill-height" fluid>
    <v-row justify="center" align="center">
      <v-col cols="12" sm="8" md="6" lg="4">
        <!-- Login Card -->
        <v-card class="elevation-4" rounded="lg">
          <!-- Header -->
          <v-card-title class="text-center pa-6">
            <div class="d-flex align-center justify-center mb-2">
              <v-icon color="primary" size="40" class="mr-3">mdi-school</v-icon>
              <h1 class="text-h4 font-weight-bold primary--text">SMIS Portal</h1>
            </div>
            <p class="text-body-1 text-medium-emphasis mt-2">Sign in to your account</p>
          </v-card-title>

          <v-divider></v-divider>

          <!-- Test Accounts Info -->
          <v-alert
            type="info"
            variant="tonal"
            class="ma-4"
            border="start"
          >
            <template v-slot:title>
              <span class="text-caption font-weight-bold">TEST ACCOUNTS (Click to copy)</span>
            </template>
            <div class="text-caption">
              <div class="account-item" @click="copyAccount('admin@school.com', 'admin123')">
                <strong>Admin:</strong> admin@school.com / admin123
                <v-icon size="16" class="ml-1">mdi-content-copy</v-icon>
              </div>
              <div class="account-item" @click="copyAccount('john.math@school.com', 'teacher123')">
                <strong>Teacher:</strong> john.math@school.com / teacher123
                <v-icon size="16" class="ml-1">mdi-content-copy</v-icon>
              </div>
              <div class="account-item" @click="copyAccount('robert@parent.com', 'parent123')">
                <strong>Parent:</strong> robert@parent.com / parent123
                <v-icon size="16" class="ml-1">mdi-content-copy</v-icon>
              </div>
            </div>
          </v-alert>

          <!-- Error Message -->
          <v-alert
            v-if="error"
            type="error"
            variant="tonal"
            class="ma-4"
          >
            {{ error }}
          </v-alert>

          <!-- Login Form -->
          <v-card-text class="pa-6">
            <v-form @submit.prevent="handleSubmit">
              <v-text-field
                v-model="form.email"
                label="Email"
                type="email"
                variant="outlined"
                :error-messages="errors.email"
                prepend-inner-icon="mdi-email"
                required
                class="mb-4"
              ></v-text-field>

              <v-text-field
                v-model="form.password"
                label="Password"
                type="password"
                variant="outlined"
                :error-messages="errors.password"
                prepend-inner-icon="mdi-lock"
                required
                class="mb-2"
              ></v-text-field>

              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                :loading="isSubmitting"
                class="mt-4"
              >
                <v-icon start>mdi-login</v-icon>
                Sign In
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>

        <!-- Footer -->
        <div class="text-center mt-4">
          <span class="text-caption text-medium-emphasis">
            © 2024 School Management System
          </span>
        </div>
      </v-col>
    </v-row>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      <div class="d-flex align-center">
        <v-icon class="mr-2">{{ snackbar.icon }}</v-icon>
        {{ snackbar.message }}
      </div>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
  icon: 'mdi-check'
})
const form = ref({
  email: '',
  password: ''
})
const errors = ref({})
const error = ref('')
const isSubmitting = ref(false)

const showSnackbar = (message, color = 'success', icon = 'mdi-check') => {
  snackbar.value = {
    show: true,
    message,
    color,
    icon
  }
}

const copyAccount = async (email, password) => {
  try {
    // Copy email and password to clipboard
    const textToCopy = `Email: ${email}\nPassword: ${password}`
    await navigator.clipboard.writeText(textToCopy)

    // Auto-fill the form
    form.value.email = email
    form.value.password = password

    showSnackbar('Account details copied and auto-filled!', 'success', 'mdi-content-copy')
  } catch (err) {
    // Fallback for browsers that don't support clipboard API
    form.value.email = email
    form.value.password = password
    showSnackbar('Account details auto-filled!', 'info', 'mdi-information')
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errors.value = {}
  error.value = ''

  try {
    const success = await authStore.login(form.value)
    if (success) {
      showSnackbar('Login successful! Redirecting to dashboard...', 'success', 'mdi-check')
      router.push('/')
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      error.value = err.response?.data?.message || 'Login failed'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.account-item {
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
}

.account-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.account-item:active {
  background-color: rgba(0, 0, 0, 0.08);
}

:deep(.v-card) {
  border-radius: 12px;
}

:deep(.v-btn) {
  text-transform: none;
  font-weight: 600;
}

:deep(.v-text-field) {
  border-radius: 8px;
}
</style>
