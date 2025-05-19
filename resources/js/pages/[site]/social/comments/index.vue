<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mt-8 mb-6">
        <h1 class="text-2xl font-bold">Comentarios</h1>
      </div>

      <!-- Filtros: Búsqueda y estado -->
      <div class="mb-4 flex flex-wrap gap-4">
        <DuiInput
          v-model="filters.q"
          placeholder="Buscar comentarios..."
          @input="handleSearch"
          class="w-72"
        >
          <template #suffix>
            <i class="mdi mdi-magnify"></i>
          </template>
        </DuiInput>

        <DuiSelect
          v-model="filters.status"
          placeholder="Filtrar por estado"
          :options="[
            { value: '', label: 'Todos los estados' },
            { value: 'approved', label: 'Aprobados' },
            { value: 'pending', label: 'Pendientes' }
          ]"
          @change="getComments"
          class="w-48"
        />

        <DuiSelect
          v-model="filters.type"
          placeholder="Filtrar por tipo"
          :options="[
            { value: '', label: 'Todos los tipos' },
            { value: 'posts', label: 'Posts' },
            { value: 'courses', label: 'Cursos' },
            { value: 'lessons', label: 'Lecciones' }
          ]"
          @change="getComments"
          class="w-48"
        />
      </div>

      <!-- Indicador de carga -->
      <div v-if="loading" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- Mensaje cuando no hay comentarios -->
      <div v-else-if="!loading && (!comments.data || comments.data.length === 0)" class="text-center py-10 bg-gray-50 rounded-md">
        <i class="mdi mdi-comment-outline text-5xl text-gray-400"></i>
        <p class="text-gray-500 mt-4">No se encontraron comentarios</p>
      </div>

      <!-- Lista de comentarios -->
      <div v-else class="space-y-4">
        <CardComment
          v-for="comment in comments.data"
          :key="comment.id"
          :comment="comment"
          :site-id="siteId"
          :is-child="false"
          @comment-updated="getComments"
        />
      </div>

      <!-- Paginación -->
      <div v-if="comments.last_page && comments.last_page > 1" class="mt-6 flex justify-between items-center">
        <div class="flex space-x-1">
          <DuiButton
            @click="changePage(comments.current_page! - 1)"
            :disabled="comments.current_page === 1"
            variant="ghost"
            size="sm"
          >
            <i class="mdi mdi-chevron-left mr-1"></i> Anterior
          </DuiButton>
          <DuiButton
            v-for="page in getPageNumbers()"
            :key="page"
            @click="changePage(page)"
            :variant="comments.current_page === page ? 'solid' : 'ghost'"
            :color="comments.current_page === page ? 'primary' : 'neutral'"
            size="sm"
          >
            {{ page }}
          </DuiButton>
          <DuiButton
            @click="changePage(comments.current_page! + 1)"
            :disabled="comments.current_page === comments.last_page"
            variant="ghost"
            size="sm"
          >
            Siguiente <i class="mdi mdi-chevron-right ml-1"></i>
          </DuiButton>
        </div>
        <div class="text-sm text-gray-500">
          {{ comments.from || 0 }}-{{ comments.to || 0 }} de {{ comments.total || 0 }} resultados
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import CardComment from '@/components/social/CardComment.vue';
import { ref, onMounted, reactive } from 'vue';
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiSelect } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;

const filters = reactive({
  q: '',
  perPage: 10,
  page: 1,
  status: '',
  type: ''
});

const comments = ref<Pagination<SocialComment[]>>({
  data: [],
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

const loading = ref(false);
const searchTimeout = ref<number | null>(null);

// Obtener los comentarios
const getComments = async () => {
  loading.value = true;
  try {
    const filtersParams = new URLSearchParams(filters as any).toString();
    const response = await appi.get<Pagination<SocialComment[]>>(`/social/comments?${filtersParams}`, {
      headers: {
        site: siteId,
      },
    });
    comments.value = response.data;
  } catch (error) {
    console.error('Error al cargar comentarios:', error);
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
    getComments();
  }, 300);
};

// Función para cambiar de página
const changePage = (page: number) => {
  if (page >= 1 && page <= (comments.value.last_page || 1)) {
    filters.page = page;
    getComments();
  }
};

// Función para generar números de página
const getPageNumbers = () => {
  const current = comments.value.current_page || 1;
  const last = comments.value.last_page || 1;

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
  getComments();
});
</script>
