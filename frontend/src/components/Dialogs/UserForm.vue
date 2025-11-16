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
                v-model="form.name"
                label="Full Name"
                :rules="[required]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.email"
                label="Email"
                type="email"
                :rules="[required, email]"
                required
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.phone"
                label="Phone"
                :rules="[phone]"
                variant="outlined"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.role"
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
                v-model="form.password"
                label="Password"
                :type="showPassword ? 'text' : 'password'"
                :rules="isEdit ? [] : [required, password]"
                :required="!isEdit"
                variant="outlined"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.password_confirmation"
                label="Confirm Password"
                :type="showPassword ? 'text' : 'password'"
                :rules="isEdit ? [] : [required, passwordMatch]"
                :required="!isEdit"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12">
              <v-switch
                v-model="form.is_active"
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

<script>
import { ref, watch } from 'vue'
import { required, email, phone, password } from '@/utils/validators'

export default {
  name: 'UserForm',
  props: {
    modelValue: Boolean,
    user: {
      type: Object,
      default: null
    }
  },
  setup(props, { emit }) {
    const dialog = ref(false)
    const form = ref({})
    const loading = ref(false)
    const isEdit = ref(false)
    const showPassword = ref(false)

    const roleOptions = [
      { title: 'Admin', value: 'admin' },
      { title: 'Teacher', value: 'teacher' },
      { title: 'Parent', value: 'parent' }
    ]

    watch(() => props.modelValue, (val) => {
      dialog.value = val
      if (val) {
        initializeForm()
      }
    })

    watch(() => props.user, (val) => {
      if (val) {
        isEdit.value = true
        form.value = { ...val }
      } else {
        isEdit.value = false
        initializeForm()
      }
    })

    const initializeForm = () => {
      form.value = props.user ? { ...props.user } : {
        name: '',
        email: '',
        phone: '',
        role: 'teacher',
        password: '',
        password_confirmation: '',
        is_active: true
      }
    }

    const passwordMatch = (value) => {
      return value === form.value.password || 'Passwords do not match'
    }

    const closeDialog = () => {
      dialog.value = false
      emit('update:modelValue', false)
      emit('close')
    }

    const submitForm = () => {
      // Remove password confirmation before sending to API
      const submitData = { ...form.value }
      if (submitData.password_confirmation) {
        delete submitData.password_confirmation
      }

      // If editing and password is empty, remove it
      if (isEdit.value && !submitData.password) {
        delete submitData.password
      }

      emit('submit', submitData)
    }

    return {
      dialog,
      form,
      loading,
      isEdit,
      showPassword,
      roleOptions,
      required,
      email,
      phone,
      password,
      passwordMatch,
      closeDialog,
      submitForm
    }
  }
}
</script>
