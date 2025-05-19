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

        <!-- Sección de Atributos -->
        <div class="mt-8">
          <h2 class="text-xl font-bold mb-4">Atributos del Post</h2>
          <div class="bg-gray-50 p-4 rounded-md mb-4">
            <div class="flex flex-wrap gap-4 mb-4">
              <DuiInput
                v-model="newAttr.name"
                placeholder="Nombre"
                class="flex-1 min-w-[200px]"
              />
              <DuiSelect
                v-model="newAttr.type"
                placeholder="Tipo"
                :options="attrTypeOptions"
                class="flex-1 min-w-[200px]"
              />
              <DuiInput
                v-model="newAttr.value"
                placeholder="Valor"
                class="flex-1 min-w-[200px]"
              />
              <DuiButton
                type="button"
                color="primary"
                @click="addAttribute"
                size="sm"
              >
                <i class="mdi mdi-plus mr-1"></i>
                Agregar atributo
              </DuiButton>
            </div>
          </div>

          <!-- Lista de atributos existentes -->
          <div v-if="!form.attrs || form.attrs.length === 0" class="text-center py-4 bg-gray-50 rounded-md">
            <p class="text-gray-500">No hay atributos definidos para este post</p>
          </div>
          <div v-else class="border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(attr, index) in form.attrs" :key="attr.id || index" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ attr.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ attr.type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ attr.value }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button type="button" @click="removeAttribute(index)" class="text-red-600 hover:text-red-900">
                      <i class="mdi mdi-delete"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- No hay modal para editar ya que no existe esa funcionalidad en la API -->
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

const post = ref<ContentPost | null>(null);
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

// Opciones para tipos de atributos
const attrTypeOptions = [
  { value: 'text', label: 'Texto' },
  { value: 'number', label: 'Número' },
  { value: 'boolean', label: 'Boolean' },
  { value: 'date', label: 'Fecha' },
  { value: 'url', label: 'URL' },
  { value: 'color', label: 'Color' },
  { value: 'json', label: 'JSON' }
];

// Estado para la gestión de atributos
const newAttr = reactive({
  name: '',
  type: 'text',
  value: ''
});

// No necesitamos variables para edición ya que no existe esa funcionalidad en la API

const form = reactive({
  name: '',
  description: '',
  picture: '',
  content: '',
  format: 'markdown',
  category: '',
  active: false,
  attrs: [] as ContentAttr[],
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
    const response = await appi.get<ContentPost>(`/content/posts/${slug}`, {
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
    form.attrs = post.value.attrs || [];
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Error al cargar el post';
    console.error('Error al cargar el post:', e);
  } finally {
    loading.value = false;
  }
};

// Función para agregar un nuevo atributo
const addAttribute = async () => {
  if (!newAttr.name.trim() || !newAttr.value.trim()) {
    // Validación simple
    error.value = 'Nombre y valor del atributo son requeridos';
    setTimeout(() => {
      error.value = '';
    }, 3000);
    return;
  }

  try {
    // Crear nuevo atributo usando el endpoint específico
    const attrData = {
      name: newAttr.name.trim(),
      type: newAttr.type,
      value: newAttr.value.trim()
    };

    loading.value = true;

    // Llamar al endpoint POST para crear el atributo
    const response = await appi.post(`/content/posts/${slug}/attrs`, attrData, {
      headers: { site: siteId },
    });

    // Agregar el atributo recibido a la lista (con su ID asignado por el backend)
    if (!form.attrs) form.attrs = [];
    form.attrs.push(response.data);

    // Mostrar mensaje de éxito
    success.value = 'Atributo agregado correctamente';
    setTimeout(() => {
      success.value = '';
    }, 3000);

    // Limpiar el formulario
    newAttr.name = '';
    newAttr.type = 'text';
    newAttr.value = '';
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Error al crear el atributo';
    console.error('Error al crear el atributo:', e);
  } finally {
    loading.value = false;
  }
};

// Función para remover un atributo
const removeAttribute = async (index: number) => {
  if (form.attrs && index >= 0 && index < form.attrs.length) {
    const attrId = form.attrs[index].id;

    try {
      loading.value = true;

      // Llamar al endpoint DELETE para eliminar el atributo
      await appi.delete(`/content/posts/${slug}/attrs/${attrId}`, {
        headers: { site: siteId },
      });

      // Eliminar de la lista local
      form.attrs.splice(index, 1);

      // Mostrar mensaje de éxito
      success.value = 'Atributo eliminado correctamente';
      setTimeout(() => {
        success.value = '';
      }, 3000);
    } catch (e: any) {
      error.value = e?.response?.data?.message || 'Error al eliminar el atributo';
      console.error('Error al eliminar el atributo:', e);
    } finally {
      loading.value = false;
    }
  }
};

// No se incluyen funciones de edición ya que la API no lo soporta

const goBack = () => {
  router.back();
};

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  submitting.value = true;
  try {
    // No incluimos los atributos en la actualización del post ya que se gestionan por separado
    await appi.put(`/content/posts/${slug}`, {
      name: form.name,
      description: form.description,
      picture: form.picture,
      content: form.content,
      format: form.format,
      category: form.category,
      site_id: siteId,
      active: form.active ? 1 : 0
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
