<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mt-8 mb-6">
        <h1 class="text-2xl font-bold">Cursos</h1>
        <RouterLink
          :to="`/app/${siteId}/learn/courses/create`"
          >
          <DuiAction color="primary">
            <i class="mdi mdi-plus-circle mr-2"></i>
            Nuevo curso
          </DuiAction>
        </RouterLink>
      </div>

      <!-- Filtros: Búsqueda -->
      <div class="mb-4 flex flex-wrap gap-4">
        <DuiInput
          v-model="filters.q"
          placeholder="Buscar cursos..."
          @input="handleSearch"
          class="w-72"
        >
          <template #suffix>
            <i class="mdi mdi-magnify"></i>
          </template>
        </DuiInput>

        <DuiSelect
          v-model="filters.open"
          placeholder="Estado"
          :options="[
            { value: '', label: 'Todos los estados' },
            { value: '1', label: 'Abiertos' },
            { value: '0', label: 'Cerrados' }
          ]"
          @change="getCourses"
          class="w-48"
        />
      </div>

      <!-- Indicador de carga -->
      <div v-if="loading" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Mensaje cuando no hay cursos -->
      <div v-else-if="!loading && (!courses.data || courses.data.length === 0)" class="text-center py-10 bg-gray-50 rounded-md">
        <i class="mdi mdi-book-open-page-variant-outline text-5xl text-gray-400"></i>
        <p class="text-gray-500 mt-4">No se encontraron cursos</p>
      </div>

      <!-- Lista de cursos -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="course in courses.data" :key="course.id" class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="relative">
            <img
              v-if="course.picture"
              :src="course.picture"
              class="w-full h-48 object-cover"
              :alt="course.name"
            />
            <div v-else class="w-full h-48 bg-gray-200 flex items-center justify-center">
              <i class="mdi mdi-image-off text-4xl text-gray-400"></i>
            </div>
            <div
              v-if="course.open"
              class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full"
            >
              Abierto
            </div>
            <div
              v-else
              class="absolute top-2 right-2 bg-gray-500 text-white text-xs px-2 py-1 rounded-full"
            >
              Cerrado
            </div>
          </div>

          <div class="p-4">
            <div class="flex items-center gap-2 mb-2">
              <div v-if="course.icon" class="w-8 h-8 flex-shrink-0">
                <img :src="course.icon" class="w-full h-full object-contain" :alt="course.name + ' icon'" />
              </div>
              <div v-else class="w-8 h-8 flex-shrink-0 bg-gray-100 rounded-md flex items-center justify-center">
                <i class="mdi mdi-school text-gray-500"></i>
              </div>
              <h2 class="text-lg font-semibold line-clamp-1">{{ course.name }}</h2>
            </div>

            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ course.description }}</p>

            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-500">
                Creado: {{ formatDate(course.created_at) }}
              </span>
              <div class="flex space-x-2">
                <RouterLink :to="`/app/${siteId}/learn/courses/${course.slug}`">
                  <DuiAction size="sm" variant="outline">
                    <i class="mdi mdi-pencil mr-1"></i> Editar
                  </DuiAction>
                </RouterLink>
                <RouterLink :to="`/app/${siteId}/learn/courses/${course.slug}/lessons`">
                  <DuiAction size="sm" variant="outline">
                    <i class="mdi mdi-book-multiple mr-1"></i> Lecciones
                  </DuiAction>
                </RouterLink>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="courses.last_page && courses.last_page > 1" class="mt-6 flex justify-between items-center">
        <div class="flex space-x-1">
          <DuiButton
            @click="changePage(courses.current_page! - 1)"
            :disabled="courses.current_page === 1"
            variant="ghost"
            size="sm"
          >
            <i class="mdi mdi-chevron-left mr-1"></i> Anterior
          </DuiButton>
          <DuiButton
            v-for="page in getPageNumbers()"
            :key="page"
            @click="changePage(page)"
            :variant="courses.current_page === page ? 'solid' : 'ghost'"
            :color="courses.current_page === page ? 'primary' : 'neutral'"
            size="sm"
          >
            {{ page }}
          </DuiButton>
          <DuiButton
            @click="changePage(courses.current_page! + 1)"
            :disabled="courses.current_page === courses.last_page"
            variant="ghost"
            size="sm"
          >
            Siguiente <i class="mdi mdi-chevron-right ml-1"></i>
          </DuiButton>
        </div>
        <div class="text-sm text-gray-500">
          {{ courses.from || 0 }}-{{ courses.to || 0 }} de {{ courses.total || 0 }} resultados
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref, onMounted, reactive } from 'vue';
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiAction, DuiButton, DuiSelect } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;

const filters = reactive({
  q: '',
  perPage: 12,
  page: 1,
  open: '',
});

const courses = ref<Pagination<LearnCourse[]>>({
  data: [],
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

const loading = ref(false);
const searchTimeout = ref<number | null>(null);

// Obtener los cursos
const getCourses = async () => {
  loading.value = true;
  try {
    const filtersParams = new URLSearchParams(filters as any).toString();
    const response = await appi.get<Pagination<LearnCourse[]>>(`/learn/courses?${filtersParams}`, {
      headers: {
        site: siteId,
      },
    });
    courses.value = response.data;
  } catch (error) {
    console.error('Error al cargar cursos:', error);
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
    filters.page = 1; // Reiniciar a la primera página al buscar
    getCourses();
  }, 300);
};

// Función para cambiar de página
const changePage = (page: number) => {
  if (page >= 1 && page <= (courses.value.last_page || 1)) {
    filters.page = page;
    getCourses();
  }
};

// Función para generar números de página
const getPageNumbers = () => {
  const current = courses.value.current_page || 1;
  const last = courses.value.last_page || 1;

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

// Función para formatear fechas
const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

onMounted(() => {
  getCourses();
});
</script>
