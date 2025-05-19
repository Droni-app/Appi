<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mt-8 mb-6">
        <h1 class="text-2xl font-bold">Categorías</h1>
        <RouterLink :to="`/app/${siteId}/content/categories/create`">
          <DuiAction color="primary">
            <i class="mdi mdi-plus"></i>
            Crear nueva categoría
          </DuiAction>
        </RouterLink>
      </div>

      <!-- Filtros: Búsqueda -->
      <div class="mb-4 flex items-center gap-4">
        <DuiInput
          v-model="filters.q"
          placeholder="Buscar categorías..."
          @input="handleSearch"
          class="w-72"
        >
          <template #suffix>
            <i class="mdi mdi-magnify"></i>
          </template>
        </DuiInput>
      </div>

      <!-- Indicador de carga -->
      <div v-if="loading" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Mensaje cuando no hay categorías -->
      <div v-else-if="!loading && (!displayedCategories || displayedCategories.length === 0)" class="text-center py-10 bg-gray-50 rounded-md">
        <i class="mdi mdi-folder-outline text-5xl text-gray-400"></i>
        <p class="text-gray-500 mt-4">No se encontraron categorías</p>
        <RouterLink :to="`/app/${siteId}/content/categories/create`" class="mt-4 inline-block">
          <DuiButton variant="solid" color="primary" size="sm">
            <i class="mdi mdi-plus mr-1"></i> Crear nueva categoría
          </DuiButton>
        </RouterLink>
      </div>

      <!-- Tabla de categorías -->
      <DuiTable
        v-else
        :rows="displayedCategories"
        :columns="[
          { name: 'id', label: '#' },
          { name: 'name', label: 'Nombre' },
          { name: 'description', label: 'Descripción' },
          { name: 'created_at', label: 'Fecha de creación' },
          { name: 'updated_at', label: 'Última actualización' },
        ]">
        <template #name="{ slug, name }">
          <RouterLink :to="`/app/${siteId}/content/categories/${slug}`" class="text-blue-600 hover:text-blue-800">
            {{ name }}
          </RouterLink>
        </template>
        <template #description="{ description }">
          <p class="text-gray-500 text-sm">
            {{ description || 'Sin descripción' }}
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
      <div v-if="pagination.totalPages > 1" class="mt-6 flex justify-between items-center">
        <div class="flex space-x-1">
          <DuiButton
            @click="changePage(pagination.currentPage - 1)"
            :disabled="pagination.currentPage === 1"
            variant="ghost"
            size="sm"
          >
            <i class="mdi mdi-chevron-left mr-1"></i> Anterior
          </DuiButton>
          <DuiButton
            v-for="page in getPageNumbers()"
            :key="page"
            @click="changePage(page)"
            :variant="pagination.currentPage === page ? 'solid' : 'ghost'"
            :color="pagination.currentPage === page ? 'primary' : 'neutral'"
            size="sm"
          >
            {{ page }}
          </DuiButton>
          <DuiButton
            @click="changePage(pagination.currentPage + 1)"
            :disabled="pagination.currentPage === pagination.totalPages"
            variant="ghost"
            size="sm"
          >
            Siguiente <i class="mdi mdi-chevron-right ml-1"></i>
          </DuiButton>
        </div>
        <div class="text-sm text-gray-500">
          {{ (pagination.currentPage - 1) * pagination.perPage + 1 }}-{{ Math.min(pagination.currentPage * pagination.perPage, pagination.totalItems) }} de {{ pagination.totalItems }} resultados
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
import { DuiTable, DuiAction, DuiInput, DuiButton } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;

const filters = ref({
  q: '',
  page: 1
});

// Todas las categorías obtenidas de la API
const allCategories = ref<ContentCategory[]>([]);
// Categorías filtradas y paginadas para mostrar en la UI
const displayedCategories = ref<ContentCategory[]>([]);

// Configuración de paginación manual
const pagination = ref({
  currentPage: 1,
  perPage: 10,
  totalPages: 1,
  totalItems: 0
});

const loading = ref(false);
const searchTimeout = ref<number | null>(null);

// Obtener todas las categorías desde la API
const getCategories = async () => {
  loading.value = true;
  try {
    const response = await appi.get<ContentCategory[]>(`/content/categories`, {
      headers: {
        site: siteId,
      },
    });

    // Guardar todas las categorías
    allCategories.value = response.data;

    // Aplicar filtros y paginación
    applyFiltersAndPagination();
  } catch (error) {
    console.error('Error al cargar categorías:', error);
  } finally {
    loading.value = false;
  }
};

// Aplicar filtros y paginación a las categorías
const applyFiltersAndPagination = () => {
  // Filtrar por búsqueda si existe
  let filtered = [...allCategories.value];

  if (filters.value.q) {
    const searchTerm = filters.value.q.toLowerCase();
    filtered = filtered.filter(category =>
      category.name.toLowerCase().includes(searchTerm) ||
      (category.description && category.description.toLowerCase().includes(searchTerm))
    );
  }

  // Actualizar total de elementos y páginas
  pagination.value.totalItems = filtered.length;
  pagination.value.totalPages = Math.ceil(filtered.length / pagination.value.perPage);

  // Calcular índices para la paginación
  const startIndex = (pagination.value.currentPage - 1) * pagination.value.perPage;
  const endIndex = Math.min(startIndex + pagination.value.perPage, filtered.length);

  // Aplicar paginación
  displayedCategories.value = filtered.slice(startIndex, endIndex);
};

// Función para manejar la búsqueda con debounce
const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = window.setTimeout(() => {
    pagination.value.currentPage = 1; // Reiniciar a la primera página al buscar
    applyFiltersAndPagination();
  }, 300);
};

// Función para cambiar de página
const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.totalPages) {
    pagination.value.currentPage = page;
    applyFiltersAndPagination();
  }
};

// Función para generar números de página
const getPageNumbers = () => {
  const current = pagination.value.currentPage;
  const last = pagination.value.totalPages;

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

onMounted(() => {
  getCategories();
});
</script>
