<template>
  <v-card variant="flat" class="mb-4">
    <v-card-text>
      <v-row align="center">
        <v-col cols="12" sm="6" md="4">
          <v-text-field
            v-model="search"
            label="Search"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filters.class"
            :items="CLASS_LEVELS"
            label="Class"
            variant="outlined"
            density="compact"
            clearable
            hide-details
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filters.section"
            :items="SECTIONS"
            label="Section"
            variant="outlined"
            density="compact"
            clearable
            hide-details
          ></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="2">
          <v-btn
            color="primary"
            variant="flat"
            @click="applyFilters"
            block
          >
            Apply
          </v-btn>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
import { ref, watch } from 'vue'
import { CLASS_LEVELS, SECTIONS } from '@/utils/constants'
import { debounce } from '@/utils/helpers'

export default {
  name: 'SearchFilter',
  emits: ['update:filters'],
  setup(props, { emit }) {
    const search = ref('')
    const filters = ref({
      class: null,
      section: null
    })

    const debouncedEmit = debounce(() => {
      emit('update:filters', {
        search: search.value,
        ...filters.value
      })
    }, 300)

    watch([search, filters], debouncedEmit, { deep: true })

    const applyFilters = () => {
      emit('update:filters', {
        search: search.value,
        ...filters.value
      })
    }

    return {
      search,
      filters,
      CLASS_LEVELS,
      SECTIONS,
      applyFilters
    }
  }
}
</script>
