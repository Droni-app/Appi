<template>
  <div>
    <AdminMenu :siteId="siteId"/>
    <h1>Show Site</h1>
    {{ categories }}sss
  </div>
</template>
<script setup lang="ts">
import { useRoute } from 'vue-router'
import { ref, onMounted } from 'vue'
import appi from '@/utils/appi'
import AdminMenu from '@/components/AdminMenu.vue'

const route = useRoute()
const siteId = route.params['site'] as string

const categories = ref(null)
const getCategories = async () => {
  const response = await appi.get('/content/categories/', {
    headers: {
      site: siteId,
    },
  })
  categories.value = response.data
}

onMounted(() => {
  getCategories()
})
</script>
