<!-- src/components/Dialogs/ConfirmDialog.vue -->
<template>
  <v-dialog v-model="dialog" max-width="400px" persistent>
    <v-card>
      <v-card-title class="text-h6">
        {{ title }}
      </v-card-title>

      <v-card-text>
        {{ message }}
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey" @click="cancel">
          Cancel
        </v-btn>
        <v-btn :color="confirmColor" @click="confirm" :loading="loading">
          {{ confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { ref, watch } from 'vue'

export default {
  name: 'ConfirmDialog',
  props: {
    modelValue: Boolean,
    title: {
      type: String,
      default: 'Confirm Action'
    },
    message: {
      type: String,
      default: 'Are you sure you want to perform this action?'
    },
    confirmText: {
      type: String,
      default: 'Confirm'
    },
    confirmColor: {
      type: String,
      default: 'primary'
    },
    loading: Boolean
  },
  setup(props, { emit }) {
    const dialog = ref(false)

    watch(() => props.modelValue, (val) => {
      dialog.value = val
    })

    const confirm = () => {
      emit('confirm')
    }

    const cancel = () => {
      dialog.value = false
      emit('update:modelValue', false)
      emit('cancel')
    }

    return {
      dialog,
      confirm,
      cancel
    }
  }
}
</script>
