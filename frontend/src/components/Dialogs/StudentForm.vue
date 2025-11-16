<template>
  <v-dialog v-model="dialog" max-width="800px" persistent>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span class="text-h5">{{ isEdit ? 'Edit Student' : 'Add New Student' }}</span>
        <v-btn icon @click="closeDialog">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.student_id"
                label="Student ID"
                :rules="[required]"
                required
                variant="outlined"
                :readonly="isEdit"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.name"
                label="Full Name"
                :rules="[required]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.email"
                label="Email"
                type="email"
                :rules="[emailValidator]"
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.roll_number"
                label="Roll Number"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="4">
              <v-select
                v-model="formData.class"
                :items="classOptions"
                label="Class"
                :rules="[required]"
                required
                variant="outlined"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="formData.section"
                :items="sectionOptions"
                label="Section"
                :rules="[required]"
                required
                variant="outlined"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="formData.gender"
                :items="genderOptions"
                label="Gender"
                :rules="[required]"
                required
                variant="outlined"
              ></v-select>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.date_of_birth"
                label="Date of Birth"
                type="date"
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-file-input
                v-model="photoFile"
                label="Student Photo"
                accept="image/*"
                variant="outlined"
                prepend-icon="mdi-camera"
                clearable
                @update:model-value="handlePhotoChange"
              ></v-file-input>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <v-row>
            <v-col cols="12">
              <h3 class="text-h6 mb-4">Parent Information</h3>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.parent_name"
                label="Parent Name"
                :rules="[required]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.parent_email"
                label="Parent Email"
                type="email"
                :rules="[emailValidator]"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="formData.parent_phone"
                label="Parent Phone"
                :rules="[required, phoneValidator]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-textarea
                v-model="formData.address"
                label="Address"
                rows="2"
                variant="outlined"
              ></v-textarea>
            </v-col>
          </v-row>

          <v-row v-if="isEdit">
            <v-col cols="12">
              <v-switch
                v-model="formData.is_active"
                label="Active Student"
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
import { ref, watch, computed, nextTick } from 'vue'

// Props
const props = defineProps({
  modelValue: Boolean,
  student: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'submit', 'close'])

// Reactive data
const dialog = ref(false)
const form = ref(null)
const photoFile = ref(null)
const loading = ref(false)
const isEdit = ref(false)

// Form data with default values
const formData = ref({
  student_id: '',
  name: '',
  email: '',
  class: '',
  section: '',
  roll_number: '',
  date_of_birth: '',
  gender: '',
  parent_name: '',
  parent_email: '',
  parent_phone: '',
  address: '',
  is_active: true
})

// Constants
const classOptions = [
  'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
  'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'
]

const sectionOptions = ['A', 'B', 'C', 'D']

const genderOptions = [
  { title: 'Male', value: 'male' },
  { title: 'Female', value: 'female' },
  { title: 'Other', value: 'other' }
]

// Validators
const required = (value) => !!value || 'This field is required.'

const emailValidator = (value) => {
  if (!value) return true // Optional field
  const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return pattern.test(value) || 'Please enter a valid email address.'
}

const phoneValidator = (value) => {
  if (!value) return true
  const pattern = /^[+]?[\d\s\-()]{10,}$/
  return pattern.test(value) || 'Please enter a valid phone number.'
}

// Watchers
watch(() => props.modelValue, (val) => {
  dialog.value = val
  if (val) {
    initializeForm()
  }
})

watch(() => props.student, (val) => {
  if (val && dialog.value) {
    isEdit.value = true
    formData.value = { ...val }
  } else {
    isEdit.value = false
  }
})

// Methods
const initializeForm = () => {
  if (props.student) {
    // Edit mode - populate with student data
    isEdit.value = true
    formData.value = {
      student_id: props.student.student_id || '',
      name: props.student.name || '',
      email: props.student.email || '',
      class: props.student.class || '',
      section: props.student.section || '',
      roll_number: props.student.roll_number || '',
      date_of_birth: props.student.date_of_birth || '',
      gender: props.student.gender || '',
      parent_name: props.student.parent_name || '',
      parent_email: props.student.parent_email || '',
      parent_phone: props.student.parent_phone || '',
      address: props.student.address || '',
      is_active: props.student.is_active !== undefined ? props.student.is_active : true
    }
  } else {
    // Add mode - reset form
    isEdit.value = false
    formData.value = {
      student_id: '',
      name: '',
      email: '',
      class: '',
      section: '',
      roll_number: '',
      date_of_birth: '',
      gender: '',
      parent_name: '',
      parent_email: '',
      parent_phone: '',
      address: '',
      is_active: true
    }
  }
  photoFile.value = null
}

const closeDialog = () => {
  dialog.value = false
  emit('update:modelValue', false)
  emit('close')
}

const handlePhotoChange = (file) => {
  photoFile.value = file
}

const submitForm = async () => {
  // Validate form
  const { valid } = await form.value.validate()

  if (!valid) {
    return
  }

  loading.value = true

  try {
    // Create FormData object to send as multipart/form-data
    const submitData = new FormData()

    // Append all form data fields
    Object.keys(formData.value).forEach(key => {
      if (formData.value[key] !== null && formData.value[key] !== undefined) {
        // Convert boolean to string for FormData
        const value = typeof formData.value[key] === 'boolean'
          ? formData.value[key].toString()
          : formData.value[key]

        submitData.append(key, value)
      }
    })

    // Append photo file if selected
    if (photoFile.value) {
      submitData.append('photo', photoFile.value)
    }

    // Emit the FormData object directly to work with your studentService
    emit('submit', submitData)

  } catch (error) {
    console.error('Form submission error:', error)
  } finally {
    loading.value = false
  }
}

// Initialize form when component mounts
nextTick(() => {
  if (props.modelValue) {
    initializeForm()
  }
})
</script>
