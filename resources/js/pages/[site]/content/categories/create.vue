<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <h1 class="text-2xl font-bold mt-8 mb-6">Crear nueva categoría</h1>
      <form @submit.prevent="handleSubmit" class="space-y-6 bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row gap-6">
          <div class="flex-1 space-y-4">
            <DuiInput v-model="form.name" label="Nombre" required class="w-full" />
            <DuiTextarea v-model="form.description" label="Descripción" rows="3" class="w-full" />
            <AttachmentInput
              v-model="form.picture"
              label="Imagen"
              :siteId="siteId"
              accept="image/*"
              :resize="{ width: 800, height: 600 }"
            />
          </div>
        </div>
        <div v-if="error" class="text-red-500">{{ error }}</div>
        <div v-if="success" class="text-green-600">{{ success }}</div>
        <div class="flex justify-between">
          <RouterLink :to="`/app/${siteId}/content/categories`">
            <DuiButton type="button" color="secondary">
              Cancelar
            </DuiButton>
          </RouterLink>
          <DuiButton type="submit" color="primary" :loading="loading">
            {{ loading ? 'Guardando...' : 'Crear categoría' }}
          </DuiButton>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import AttachmentInput from '@/components/AttachmentInput.vue';
import { ref, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiTextarea } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;

const loading = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
  name: '',
  description: '',
  picture: '',
});

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  loading.value = true;
  try {
    await appi.post(`/content/categories`, {
      ...form,
      site_id: siteId,
    }, {
      headers: { site: siteId }
    });

    success.value = 'Categoría creada con éxito';

    // Redireccionar después de un breve retraso
    setTimeout(() => {
      router.push(`/app/${siteId}/content/categories`);
    }, 1500);

  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al crear la categoría';
  } finally {
    loading.value = false;
  }
};
</script>
