<template>
  <div class="container mx-auto px-4">
    <div v-if="me && me.enrollments && me.enrollments.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mt-8">
      <div v-for="enrollment in me.enrollments" :key="enrollment.id" class="bg-white rounded-lg shadow p-6 flex flex-col">
        <div class="flex items-center gap-3 mb-4">
          <img v-if="enrollment.site && enrollment.site.logo" :src="enrollment.site.logo" alt="Logo" class="w-10 h-10 rounded-full object-cover border" />
          <div>
            <RouterLink :to="`/app/${enrollment.site_id}`" class="text-lg font-semibold text-gray-800 hover:text-blue-600 transition-colors duration-200">
              {{ enrollment.site?.name || 'Sitio' }}
            </RouterLink>
            <p class=" text-sm text-gray-500">
              {{ enrollment.site?.domain }} |
              Rol: <span class="font-medium">{{ enrollment.role }}</span>
            </p>
            <p class="text-gray-500">
              {{ enrollment.site?.description || 'No hay descripción' }}
            </p>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="me && me.enrollments && !me.enrollments.length" class="mt-8 text-gray-500 text-center">
      No tienes inscripciones aún.
    </div>
  </div>
</template>
<script setup lang="ts">
import appi from "@/utils/appi";
import { ref, type Ref } from "vue";


const SiteId = import.meta.env.VITE_APPI_SITE_ID;
const me:Ref<AuthMe|null> = ref(null);

const getMe = async () => {
  const response = await appi.get<AuthMe>("/auth/me", {
    headers: {
      site: SiteId,
    },
  });
  me.value = response.data;
};

getMe();


</script>
