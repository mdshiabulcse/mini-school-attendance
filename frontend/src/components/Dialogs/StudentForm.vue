<!-- src/components/Dialogs/StudentForm.vue -->
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
                v-model="form.student_id"
                label="Student ID"
                :rules="[required]"
                required
                variant="outlined"
                :readonly="isEdit"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.name"
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
                v-model="form.email"
                label="Email"
                type="email"
                :rules="[email]"
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.roll_number"
                label="Roll Number"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="4">
              <v-select
                v-model="form.class"
                :items="classOptions"
                label="Class"
                :rules="[required]"
                required
                variant="outlined"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="form.section"
                :items="sectionOptions"
                label="Section"
                :rules="[required]"
                required
                variant="outlined"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="4">
              <v-select
                v-model="form.gender"
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
                v-model="form.date_of_birth"
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
                v-model="form.parent_name"
                label="Parent Name"
                :rules="[required]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.parent_email"
                label="Parent Email"
                type="email"
                :rules="[email]"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.parent_phone"
                label="Parent Phone"
                :rules="[required, phone]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-textarea
                v-model="form.address"
                label="Address"
                rows="2"
                variant="outlined"
              ></v-textarea>
            </v-col>
          </v-row>

          <v-row v-if="isEdit">
            <v-col cols="12">
              <v-switch
                v-model="form.is_active"
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

<script>
import { ref, watch } from 'vue'
import { required, email, phone } from '@/utils/validators'

export default {
  name: 'StudentForm',
  props: {
    modelValue: Boolean,
    student: {
      type: Object,
      default: null
    }
  },
  setup(props, { emit }) {
    const dialog = ref(false)
    const form = ref({})
    const photoFile = ref(null)
    const loading = ref(false)
    const isEdit = ref(false)

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

    watch(() => props.modelValue, (val) => {
      dialog.value = val
      if (val) {
        initializeForm()
      }
    })

    watch(() => props.student, (val) => {
      if (val) {
        isEdit.value = true
        form.value = { ...val }
      } else {
        isEdit.value = false
        initializeForm()
      }
    })

    const initializeForm = () => {
      form.value = props.student ? { ...props.student } : {
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
      photoFile.value = null
    }

    const closeDialog = () => {
      dialog.value = false
      emit('update:modelValue', false)
      emit('close')
    }

    const submitForm = () => {
      const submitData = new FormData()

      // Append form data
      Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && form.value[key] !== undefined) {
          submitData.append(key, form.value[key])
        }
      })

      // Append photo file if selected
      if (photoFile.value) {
        submitData.append('photo', photoFile.value)
      }

      emit('submit', submitData)
    }

    return {
      dialog,
      form,
      photoFile,
      loading,
      isEdit,
      classOptions,
      sectionOptions,
      genderOptions,
      required,
      email,
      phone,
      closeDialog,
      submitForm
    }
  }
}
</script>
