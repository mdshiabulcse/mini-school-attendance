<template>
  <v-container class="fill-height">
    <v-row justify="center" align="center">
      <v-col cols="12" class="text-center">
        <v-icon size="128" color="grey-lighten-1">mdi-alert-circle-outline</v-icon>
        <h1 class="text-h2 mt-4">404</h1>
        <p class="text-h5 mt-2">Page Not Found</p>
        <p class="text-body-1 mt-2 text-medium-emphasis">
          The page you are looking for does not exist.
        </p>
        <v-btn color="primary" @click="$router.push('/')" class="mt-4">
          Go to Dashboard
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'NotFoundView'
}
</script>
