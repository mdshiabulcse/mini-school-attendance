<!-- src/components/Dialogs/UserForm.vue -->
<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span class="text-h5">{{ isEdit ? 'Edit User' : 'Add New User' }}</span>
        <v-btn icon @click="closeDialog">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.name"
                label="Full Name"
                :rules="[required]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.email"
                label="Email"
                type="email"
                :rules="[required, emailValidator]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.phone"
                label="Phone"
                :rules="[phoneValidator]"
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="formData.role"
                :items="roleOptions"
                label="Role"
                :rules="[required]"
                required
                variant="outlined"
              ></v-select>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.password"
                label="Password"
                :type="showPassword ? 'text' : 'password'"
                :rules="isEdit ? [] : [required, passwordValidator]"
                :required="!isEdit"
                variant="outlined"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.password_confirmation"
                label="Confirm Password"
                :type="showPassword ? 'text' : 'password'"
                :rules="isEdit ? [] : [required, passwordMatch]"
                :required="!isEdit"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row v-if="isEdit">
            <v-col cols="12">
              <v-switch
                v-model="formData.is_active"
                label="Active User"
                color="success"
                hide-details
              ></v-switch>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey" variant="text" @click="closeDialog">
          Cancel
        </v-btn>
        <v-btn color="primary" @click="submitForm" :loading="loading">
          {{ isEdit ? 'Update' : 'Save' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

// Props
const props = defineProps({
  modelValue: Boolean,
  user: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'submit', 'close'])

// Reactive data
const dialog = ref(false)
const form = ref(null)
const loading = ref(false)
const isEdit = ref(false)
const showPassword = ref(false)

// Form data with default values
const formData = ref({
  name: '',
  email: '',
  phone: '',
  role: 'teacher',
  password: '',
  password_confirmation: '',
  is_active: true
})

// Constants
const roleOptions = [
  { title: 'Admin', value: 'admin' },
  { title: 'Teacher', value: 'teacher' },
  { title: 'Parent', value: 'parent' }
]

// Validators
const required = (value) => !!value || 'This field is required.'

const emailValidator = (value) => {
  const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return pattern.test(value) || 'Please enter a valid email address.'
}

const phoneValidator = (value) => {
  if (!value) return true
  const pattern = /^[+]?[\d\s\-()]{10,}$/
  return pattern.test(value) || 'Please enter a valid phone number.'
}

const passwordValidator = (value) => {
  return value.length >= 8 || 'Password must be at least 8 characters long'
}

const passwordMatch = (value) => {
  return value === formData.value.password || 'Passwords do not match'
}

// Watchers
watch(() => props.modelValue, (val) => {
  dialog.value = val
  if (val) {
    initializeForm()
  }
})

watch(() => props.user, (val) => {
  if (val && dialog.value) {
    isEdit.value = true
    formData.value = { ...val }
  } else {
    isEdit.value = false
  }
})

// Methods
const initializeForm = () => {
  console.log('Initializing user form with:', props.user)

  if (props.user && isEdit.value) {
    // Edit mode - populate with user data
    formData.value = {
      name: props.user.name || '',
      email: props.user.email || '',
      phone: props.user.phone || '',
      role: props.user.role || 'teacher',
      password: '',
      password_confirmation: '',
      is_active: props.user.is_active !== undefined ? props.user.is_active : true
    }
  } else {
    // Add mode - reset form
    formData.value = {
      name: '',
      email: '',
      phone: '',
      role: 'teacher',
      password: '',
      password_confirmation: '',
      is_active: true
    }
  }
}

const closeDialog = () => {
  dialog.value = false
  emit('update:modelValue', false)
  emit('close')
}

const submitForm = async () => {
  // Validate form
  const { valid } = await form.value.validate()

  if (!valid) {
    return
  }

  loading.value = true

  try {
    // Prepare data for submission
    const submitData = { ...formData.value }

    // Remove password confirmation before sending to API
    if (submitData.password_confirmation) {
      delete submitData.password_confirmation
    }

    // If editing and password is empty, remove it
    if (isEdit.value && !submitData.password) {
      delete submitData.password
    }

    console.log('Submitting user data:', submitData)

    // Emit the data
    emit('submit', submitData)

  } catch (error) {
    console.error('Form submission error:', error)
  } finally {
    loading.value = false
  }
}

// Initialize form when dialog opens
watch(dialog, (val) => {
  if (val) {
    initializeForm()
  }
})
</script>
