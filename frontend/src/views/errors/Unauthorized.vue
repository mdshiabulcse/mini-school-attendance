<template>
  <v-container class="fill-height">
    <v-row justify="center" align="center">
      <v-col cols="12" class="text-center">
        <v-icon size="128" color="warning">mdi-shield-alert</v-icon>
        <h1 class="text-h2 mt-4">403</h1>
        <p class="text-h5 mt-2">Unauthorized Access</p>
        <p class="text-body-1 mt-2 text-medium-emphasis">
          You don't have permission to access this page.
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
  name: 'UnauthorizedView'
}
</script>
