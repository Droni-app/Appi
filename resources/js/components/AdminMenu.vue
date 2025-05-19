<template>
  <nav v-if="user().email" class="bg-white shadow-md mb-6">
    <ul class="flex items-center justify-center gap-6 py-4">
      <li v-for="(item, idx) in menu" :key="item.name" class="relative">
        <template v-if="item.items && item.items.length">
          <button
            class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 focus:outline-none flex items-center gap-1"
            type="button"
            @click="toggleDropdown(idx)"
            :aria-expanded="openDropdown === idx"
          >
            {{ item.name }}
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <ul v-if="openDropdown === idx" class="absolute left-0 mt-2 min-w-[180px] bg-white border rounded shadow-lg z-20">
            <li v-for="sub in item.items" :key="sub.name">
              <router-link
                :to="`/app/${props.siteId}${sub.path}`"
                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                @click="closeDropdown"
              >
                {{ sub.name }}
              </router-link>
            </li>
          </ul>
        </template>
        <template v-else>
          <router-link
            :to="item.path"
            class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200"
          >
            {{ item.name }}
          </router-link>
        </template>
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
</template>
<script setup lang="ts">
import { ref } from 'vue'
import { DuiButton } from '@dronico/droni-kit'
import { user, logout } from '@/utils/auth'

const props = defineProps({
  siteId: {
    type: String,
    required: true,
  },
})

const menu = [
  { name: 'Home', path: '/' },
  { name: 'Content', path: '#', items: [
      { name: 'Categories', path: '/content/categories' },
      { name: 'Posts', path: '/content/posts' },
    ],
  },
]

const openDropdown = ref<number|null>(null)
const toggleDropdown = (idx:number) => {
  openDropdown.value = openDropdown.value === idx ? null : idx
}
const closeDropdown = () => {
  openDropdown.value = null
}

const logoutUser = () => {
  logout()
  window.location.reload()
}
</script>
