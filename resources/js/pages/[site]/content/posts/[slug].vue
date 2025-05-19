<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <h1 class="text-2xl font-bold mt-8 mb-6">Editar post</h1>
      <div v-if="loading && !post" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>
      <form v-else @submit.prevent="handleSubmit" class="space-y-6 bg-white rounded-lg shadow p-6">
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
            <DuiSelect
              v-model="form.format"
              label="Formato"
              :options="[
                { value: 'markdown', label: 'Markdown' },
                { value: 'html', label: 'HTML' },
                { value: 'text', label: 'Texto plano' }
              ]"
              class="w-full"
            />
            <DuiSelect
              v-model="form.category"
              label="Categoría"
              :options="categoryOptions"
              placeholder="Selecciona una categoría"
              required
              class="w-full"
            />
            <div class="mt-4">
              <label class="flex items-center cursor-pointer">
                <input v-model="form.active" type="checkbox" class="dui-checkbox mr-2 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-gray-700 font-medium">Publicar inmediatamente</span>
              </label>
            </div>
          </div>
          <!-- Columna 2: solo Contenido -->
          <div class="flex-1 space-y-4">
            <DuiTextarea v-model="form.content" label="Contenido" rows="14" required />
          </div>
        </div>
        <div v-if="error" class="text-red-500">{{ error }}</div>
        <div v-if="success" class="text-green-600">{{ success }}</div>
        <div class="flex justify-between">
          <DuiButton type="button" color="secondary" @click="goBack">
            Cancelar
          </DuiButton>
          <DuiButton type="submit" color="primary" :loading="submitting">
            {{ submitting ? 'Guardando...' : 'Actualizar post' }}
          </DuiButton>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import AttachmentInput from '@/components/AttachmentInput.vue';
import { ref, onMounted, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiTextarea, DuiSelect } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;
const slug = route.params['slug'] as string;

const post = ref<Post | null>(null);
const categories = ref<Category[]>([]);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const success = ref('');

// Opciones formateadas para el DuiSelect de categorías
const categoryOptions = computed(() =>
  categories.value.map(cat => ({
    value: cat.slug,
    label: cat.name
  }))
);

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

const getPost = async () => {
  loading.value = true;
  try {
    const response = await appi.get<Post>(`/content/posts/${slug}`, {
      headers: { site: siteId },
    });
    post.value = response.data;

    // Actualizar el formulario con los datos del post
    form.name = post.value.name;
    form.description = post.value.description || '';
    form.picture = post.value.picture || '';
    form.content = post.value.content;
    form.format = post.value.format;
    form.category = post.value.category.slug;
    form.active = post.value.active === 1;
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Error al cargar el post';
    console.error('Error al cargar el post:', e);
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  router.back();
};

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  submitting.value = true;
  try {
    await appi.put(`/content/posts/${slug}`, {
      ...form,
      site_id: siteId,
      active: form.active ? 1 : 0,
    }, {
      headers: { site: siteId },
    });
    success.value = '¡Post actualizado exitosamente!';

    // Breve pausa para mostrar el mensaje de éxito antes de redirigir
    setTimeout(() => {
      // Redirigir al listado de posts
      router.push(`/app/${siteId}/content/posts`);
    }, 1000);
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Error al actualizar el post';
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  await Promise.all([getCategories(), getPost()]);
});
</script>
