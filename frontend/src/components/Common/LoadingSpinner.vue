<template>
  <div v-if="loading" class="loading-overlay">
    <v-progress-circular
      indeterminate
      size="64"
      color="primary"
    ></v-progress-circular>
    <div v-if="message" class="text-caption mt-2">{{ message }}</div>
  </div>
</template>

<script>
export default {
  name: 'LoadingSpinner',
  props: {
    loading: Boolean,
    message: String
  }
}
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
</style>
