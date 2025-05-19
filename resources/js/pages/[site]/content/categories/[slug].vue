<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <h1 class="text-2xl font-bold mt-8 mb-6">Editar categoría</h1>
      <div v-if="loading && !category" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>
      <form v-else @submit.prevent="handleSubmit" class="space-y-6 bg-white rounded-lg shadow p-6">
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
          <DuiButton type="button" color="secondary" @click="goBack">
            Cancelar
          </DuiButton>
          <DuiButton type="submit" color="primary" :loading="submitting">
            {{ submitting ? 'Guardando...' : 'Actualizar categoría' }}
          </DuiButton>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import AttachmentInput from '@/components/AttachmentInput.vue';
import { ref, onMounted, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiTextarea } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;
const slug = route.params['slug'] as string;

const category = ref<ContentCategory | null>(null);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
  name: '',
  description: '',
  picture: '',
});

const getCategory = async () => {
  loading.value = true;
  try {
    const response = await appi.get<ContentCategory>(`/content/categories/${slug}`, {
      headers: { site: siteId },
    });
    category.value = response.data;

    // Actualizar el formulario con los datos recibidos
    form.name = category.value.name;
    form.description = category.value.description || '';
    form.picture = category.value.picture || '';

  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al cargar la categoría';
    router.push(`/app/${siteId}/content/categories`);
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  submitting.value = true;
  try {
    await appi.put(`/content/categories/${slug}`, {
      ...form,
      site_id: siteId,
    }, {
      headers: { site: siteId }
    });

    success.value = 'Categoría actualizada con éxito';

    // Actualizar la categoría en memoria
    if (category.value) {
      category.value.name = form.name;
      category.value.description = form.description;
      category.value.picture = form.picture;
    }

    setTimeout(() => {
      success.value = '';
    }, 3000);
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al actualizar la categoría';
  } finally {
    submitting.value = false;
  }
};

const goBack = () => {
  router.push(`/app/${siteId}/content/categories`);
};

onMounted(() => {
  getCategory();
});
</script>
