<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex items-center mt-8 mb-6">
        <RouterLink :to="`/app/${siteId}`">
          <DuiAction variant="ghost" class="mr-4">
            <i class="mdi mdi-arrow-left"></i>
          </DuiAction>
        </RouterLink>
        <h1 class="text-2xl font-bold">Editar Sitio</h1>
      </div>

      <form @submit.prevent="updateSite" class="bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <DuiInput v-model="site.name" label="Nombre del Sitio" required />
          <DuiInput v-model="site.domain" label="Dominio" required />
          <DuiTextarea v-model="site.description" label="Descripción" rows="4" />
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Logo</label>
            <AttachmentInput v-model="site.logo" :siteId="siteId" placeholder="Sube un logo" />
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Icono</label>
            <AttachmentInput v-model="site.icon" :siteId="siteId" placeholder="Sube un icono" />
          </div>
        </div>

        <div class="flex justify-between mt-6 gap-2">
          <RouterLink :to="`/app/${siteId}`">
            <DuiAction type="button" variant="ghost">Cancelar</DuiAction>
          </RouterLink>
          <DuiButton type="submit" color="primary" :loading="saving">Guardar</DuiButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import AttachmentInput from '@/components/AttachmentInput.vue';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiTextarea, DuiAction } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;

const site = ref<Partial<Site>>({
  name: '',
  domain: '',
  description: '',
  logo: null,
  icon: null
});
const saving = ref(false);
const loading = ref(false);
const loadError = ref('');

const fetchSite = async () => {
  loading.value = true;
  loadError.value = '';
  try {
    const { data } = await appi.get<Site>(`/sites/${siteId}`, { headers: { site: siteId } });
    site.value = data;
  } catch (e: any) {
    console.error(e);
    loadError.value = e.response?.data?.message || 'Error al cargar datos del sitio.';
  } finally {
    loading.value = false;
  }
};

const updateSite = async () => {
  saving.value = true;
  try {
    await appi.put(`/sites/${siteId}`, site.value, { headers: { site: siteId } });
    router.push(`/app/${siteId}`);
  } catch (e: any) {
    console.error(e);
    alert('Error al guardar cambios en el sitio.');
  } finally {
    saving.value = false;
  }
};

onMounted(fetchSite);
</script>
