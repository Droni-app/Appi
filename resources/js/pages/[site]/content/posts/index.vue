<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mt-8 mb-6">
        <h1 class="text-2xl font-bold">Posts</h1>
        <RouterLink :to="`/app/${siteId}/content/posts/create`">
          <DuiAction color="primary">
            <i class="mdi mdi-plus"></i>
            Crear nuevo post
          </DuiAction>
        </RouterLink>
      </div>

      <!-- Filtros: Búsqueda y Categoría -->
      <div class="mb-4 flex items-center gap-4">
        <DuiInput
          v-model="filters.q"
          placeholder="Buscar posts..."
          @input="handleSearch"
          class="w-72"
        >
          <template #suffix>
            <i class="mdi mdi-magnify"></i>
          </template>
        </DuiInput>

        <DuiSelect
          v-model="filters.category"
          placeholder="Filtrar por categoría"
          :options="categoryOptions"
          class="w-64"
          @update:modelValue="getPosts"
        />
      </div>

      <!-- Indicador de carga -->
      <div v-if="loading" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Mensaje cuando no hay posts -->
      <div v-else-if="!loading && (!posts.data || posts.data.length === 0)" class="text-center py-10 bg-gray-50 rounded-md">
        <i class="mdi mdi-file-document-outline text-5xl text-gray-400"></i>
        <p class="text-gray-500 mt-4">No se encontraron posts</p>
        <RouterLink :to="`/app/${siteId}/content/posts/create`" class="mt-4 inline-block">
          <DuiButton variant="solid" color="primary" size="sm">
            <i class="mdi mdi-plus mr-1"></i> Crear nuevo post
          </DuiButton>
        </RouterLink>
      </div>

      <!-- Tabla de posts -->
      <DuiTable
        v-else
        :rows="posts.data"
        :columns="[
          { name: 'id', label: '#' },
          { name: 'name', label: 'Título' },
          { name: 'category', label: 'Categoría' },
          { name: 'created_at', label: 'Fecha de creación' },
          { name: 'updated_at', label: 'Última actualización' },
        ]">
        <template #name="{ slug, name, description }">
          <RouterLink :to="`/app/${siteId}/content/posts/${slug}`" class="text-blue-600 hover:text-blue-800">
            {{ name }}
          </RouterLink>
          <p class="text-gray-500 text-sm">
            {{ description }}
          </p>
        </template>
        <template #category="{ category }">
          <p class="text-gray-500 text-sm">
            {{ category.name }}
          </p>
        </template>
        <template #created_at="{ created_at }">
          <p class="text-gray-500 text-sm">
            {{ new Date(created_at).toLocaleString() }}
          </p>
        </template>
        <template #updated_at="{ updated_at }">
          <p class="text-gray-500 text-sm">
            {{ new Date(updated_at).toLocaleString() }}
          </p>
        </template>
      </DuiTable>

      <!-- Paginación -->
      <div v-if="posts.last_page && posts.last_page > 1" class="mt-6 flex justify-between items-center">
        <div class="flex space-x-1">
          <DuiButton
            @click="changePage(posts.current_page! - 1)"
            :disabled="posts.current_page === 1"
            variant="ghost"
            size="sm"
          >
            <i class="mdi mdi-chevron-left mr-1"></i> Anterior
          </DuiButton>
          <DuiButton
            v-for="page in getPageNumbers()"
            :key="page"
            @click="changePage(page)"
            :variant="posts.current_page === page ? 'solid' : 'ghost'"
            :color="posts.current_page === page ? 'primary' : 'neutral'"
            size="sm"
          >
            {{ page }}
          </DuiButton>
          <DuiButton
            @click="changePage(posts.current_page! + 1)"
            :disabled="posts.current_page === posts.last_page"
            variant="ghost"
            size="sm"
          >
            Siguiente <i class="mdi mdi-chevron-right ml-1"></i>
          </DuiButton>
        </div>
        <div class="text-sm text-gray-500">
          {{ posts.from || 0 }}-{{ posts.to || 0 }} de {{ posts.total || 0 }} resultados
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';
import { DuiTable, DuiAction, DuiInput, DuiButton, DuiSelect } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;

// Categorías disponibles
const categories = ref<{ slug: string; name: string }[]>([]);
const categoryOptions = computed(() =>
  [{ value: '', label: 'Todas las categorías' }].concat(
    categories.value.map(cat => ({
      value: cat.slug,
      label: cat.name
    }))
  )
);

const filters = ref({
  q: '',
  category: '',
  perPage: 10,
  page: 1
});

const posts = ref<Pagination<ContentPost[]>>({
  data: [],
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

const loading = ref(false);
const searchTimeout = ref<number | null>(null);

const getPosts = async () => {
  loading.value = true;
  try {
    const filtersParams = new URLSearchParams(filters.value as any).toString();
    const response = await appi.get<Pagination<ContentPost[]>>(`/content/posts?${filtersParams}`, {
      headers: {
        site: siteId,
      },
    });
    posts.value = response.data;
  } catch (error) {
    console.error('Error al cargar posts:', error);
  } finally {
    loading.value = false;
  }
};

// Función para manejar la búsqueda con debounce
const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = window.setTimeout(() => {
    filters.value.page = 1; // Reiniciar a la primera página al buscar
    getPosts();
  }, 300);
};

// Función para cambiar de página
const changePage = (page: number) => {
  if (page >= 1 && page <= (posts.value.last_page || 1)) {
    filters.value.page = page;
    getPosts();
  }
};

// Función para generar números de página
const getPageNumbers = () => {
  const current = posts.value.current_page || 1;
  const last = posts.value.last_page || 1;

  if (last <= 5) {
    return Array.from({ length: last }, (_, i) => i + 1);
  }

  if (current <= 3) {
    return [1, 2, 3, 4, 5];
  }

  if (current >= last - 2) {
    return [last - 4, last - 3, last - 2, last - 1, last];
  }

  return [current - 2, current - 1, current, current + 1, current + 2];
};

// Obtener categorías para el filtro
const getCategories = async () => {
  try {
    const response = await appi.get('/content/categories', {
      headers: {
        site: siteId,
      },
    });
    categories.value = response.data;
  } catch (error) {
    console.error('Error al cargar categorías:', error);
  }
};

onMounted(async () => {
  await getCategories();
  getPosts();
});

</script>
