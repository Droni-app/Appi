<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
    </div>
  </div>
</template>
<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';

const route = useRoute();
const siteId = route.params['site'] as string;
</script>
