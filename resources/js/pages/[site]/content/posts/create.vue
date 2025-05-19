<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <h1 class="text-2xl font-bold mt-8 mb-6">Crear nuevo post</h1>
      <form @submit.prevent="handleSubmit" class="space-y-6 bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Columna 1: todos los campos excepto Contenido -->
          <div class="flex-1 space-y-4">
            <DuiInput v-model="form.name" label="Título" required class="w-full" />
            <DuiTextarea v-model="form.description" label="Descripción" rows="2" class="w-full" />
            <AttachmentInput
              v-model="form.picture"
              label="Imagen destacada"
              :siteId="siteId"
              accept="image/*"
              :resize="{ width: 1200, height: 630 }"
            />
            <div>
              <label class="block font-medium mb-1">Formato</label>
              <select v-model="form.format" class="dui-input w-full">
                <option value="markdown">Markdown</option>
                <option value="html">HTML</option>
                <option value="text">Texto plano</option>
              </select>
            </div>
            <div>
              <label class="block font-medium mb-1">Categoría</label>
              <!-- Se utiliza el slug de la categoría como valor, no el ID -->
              <select v-model="form.category" class="dui-input w-full" required>
                <option value="">Selecciona una categoría</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-medium mb-1">¿Activo?</label>
              <input v-model="form.active" type="checkbox" class="dui-checkbox mr-2" />
              <span class="text-gray-700">Publicar inmediatamente</span>
            </div>
          </div>
          <!-- Columna 2: solo Contenido -->
          <div class="flex-1 space-y-4">
            <DuiTextarea v-model="form.content" label="Contenido" rows="14" required />
          </div>
        </div>
        <div v-if="error" class="text-red-500">{{ error }}</div>
        <div v-if="success" class="text-green-600">{{ success }}</div>
        <div>
          <DuiButton type="submit" color="primary" block :loading="loading">
            {{ loading ? 'Guardando...' : 'Crear post' }}
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
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiTextarea } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;

const categories = ref<Category[]>([]);
const loading = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
  name: '',
  description: '',
  picture: '',
  content: '',
  format: 'markdown',
  category: '',
  active: false,
});

const getCategories = async () => {
  try {
    const response = await appi.get<Category[]>(`/content/categories`, {
      headers: { site: siteId },
    });
    categories.value = response.data;
  } catch (e) {
    categories.value = [];
  }
};

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  loading.value = true;
  try {
    await appi.post(`/content/posts`, {
      ...form,
      site_id: siteId,
      active: form.active ? 1 : 0,
    }, {
      headers: { site: siteId },
    });
    success.value = '¡Post creado exitosamente!';
    Object.assign(form, { name: '', description: '', picture: '', content: '', format: 'markdown', category: '', active: false });
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Error al crear el post';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  getCategories();
});
</script>
