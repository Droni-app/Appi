<template>
  <nav v-if="user().email" class="bg-white shadow-md mb-6">
    <ul class="flex items-center justify-center gap-6 py-4">
      <li>
        <router-link to="/" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Home</router-link>
      </li>
      <li>
        <router-link to="/" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">About</router-link>
      </li>
      <li>
        <router-link to="/" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Contact</router-link>
      </li>
      <li>
        <DuiButton
          variant="ghost"
          @click="logoutUser"
          class="logout-button text-gray-700 hover:text-red-500 transition-colors duration-200"
        >
          <i class="mdi mdi-logout"></i>
        </DuiButton>
      </li>
    </ul>
  </nav>
  <main>
    <RouterView />
  </main>
</template>
<script setup lang="ts">
import { DuiButton } from '@dronico/droni-kit'
import { user, logout } from './utils/auth'

const logoutUser = () => {
  logout()
  window.location.reload()
}
</script>
