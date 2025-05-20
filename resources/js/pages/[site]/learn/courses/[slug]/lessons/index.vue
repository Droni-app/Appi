<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mt-8 mb-6">
        <h1 class="text-2xl font-bold">Lecciones</h1>
        <RouterLink :to="`/app/${siteId}/learn/courses/${courseSlug}/lessons/create`">
          <DuiAction color="primary">
            <i class="mdi mdi-plus"></i> Crear lección
          </DuiAction>
        </RouterLink>
      </div>

      <!-- Filtros: Búsqueda y estado -->
      <div class="mb-4 flex items-center gap-4">
        <DuiInput
          v-model="filters.q"
          placeholder="Buscar lecciones..."
          @input="handleSearch"
          class="w-72"
        >
          <template #suffix><i class="mdi mdi-magnify"></i></template>
        </DuiInput>
        <DuiSelect
          v-model="filters.active"
          placeholder="Filtrar por estado"
          :options="[
            { value: '', label: 'Todos' },
            { value: '1', label: 'Activas' },
            { value: '0', label: 'Inactivas' }
          ]"
          @change="getLessons"
          class="w-48"
        />
      </div>

      <!-- Carga -->
      <div v-if="loading" class="flex justify-center py-10">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
      </div>

      <!-- No hay lecciones -->
      <div v-else-if="!loading && lessons.data.length === 0" class="text-center py-10 bg-gray-50 rounded-md">
        <i class="mdi mdi-book-open-page-variant-outline text-5xl text-gray-400"></i>
        <p class="text-gray-500 mt-4">No se encontraron lecciones</p>
      </div>

      <!-- Tabla de lecciones -->
      <DuiTable
        v-else
        :rows="lessons.data"
        :columns="[
          { name: 'position', label: '#pos' },
          { name: 'name', label: 'Nombre' },
          { name: 'type', label: 'Tipo' },
          { name: 'active', label: 'Activo' },
          { name: 'created_at', label: 'Creado' },
          { name: 'updated_at', label: 'Actualizado' }
        ]">
        <template #name="{ slug, name, description }">
          <RouterLink :to="`/app/${siteId}/learn/courses/${courseSlug}/lessons/${slug}`" class="text-blue-600 hover:underline">
            {{ name }}
          </RouterLink>
          <small class="text-gray-500 block">{{ description }}</small>
        </template>
        <template #type="{ type }">
          <span class="text-sm text-gray-500">{{ type }}</span>
        </template>
        <template #active="{ active }">
          <span :class="active ? 'text-green-600' : 'text-red-600'">
            {{ active ? 'Sí' : 'No' }}
          </span>
        </template>
        <template #created_at="{ created_at }">
          {{ new Date(created_at).toLocaleString() }}
        </template>
        <template #updated_at="{ updated_at }">
          {{ new Date(updated_at).toLocaleString() }}
        </template>
      </DuiTable>

      <!-- Paginación -->
      <div v-if="lessons.last_page && lessons.last_page > 1" class="mt-6 flex justify-between items-center">
        <div class="flex space-x-1">
          <DuiButton @click="changePage(lessons.current_page! -1)" :disabled="lessons.current_page===1" size="sm" variant="ghost">
            <i class="mdi mdi-chevron-left"></i>
          </DuiButton>
          <DuiButton v-for="page in getPageNumbers()" :key="page" @click="changePage(page)" size="sm"
            :variant="lessons.current_page===page?'solid':'ghost'" :color="lessons.current_page===page?'primary':'neutral'">
            {{ page }}
          </DuiButton>
          <DuiButton @click="changePage(lessons.current_page! +1)" :disabled="lessons.current_page===lessons.last_page" size="sm" variant="ghost">
            <i class="mdi mdi-chevron-right"></i>
          </DuiButton>
        </div>
        <div class="text-sm text-gray-500">
          {{ lessons.from }}-{{ lessons.to }} de {{ lessons.total }} resultados
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';
import { DuiTable, DuiInput, DuiButton, DuiSelect, DuiAction } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;
const courseSlug = route.params['slug'] as string;

const filters = ref({ q: '', active: '', perPage: 10, page: 1 });
const lessons = ref<Pagination<LearnLesson[]>>({ data: [], current_page:1, last_page:1, from:0, to:0, total:0 });
const loading = ref(false);
let searchTimeout: number | null = null;

const getLessons = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams(filters.value as any).toString();
    const { data } = await appi.get<Pagination<LearnLesson[]>>(
      `/learn/courses/${courseSlug}/lessons?${params}`, { headers: { site: siteId }}
    );
    lessons.value = data;
  } catch(e) { console.error(e); } finally { loading.value = false; }
};

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = window.setTimeout(()=>{ filters.value.page=1; getLessons();},300);
};

const changePage = (page:number) => { if(page>=1 && page<= lessons.value.last_page!) { filters.value.page=page; getLessons(); } };
const getPageNumbers = () => { const current=lessons.value.current_page!, last=lessons.value.last_page!;
  if(last<=5) return Array.from({length:last},(_,i)=>i+1);
  if(current<=3) return [1,2,3,4,5];
  if(current>=last-2) return [last-4,last-3,last-2,last-1,last];
  return [current-2,current-1,current,current+1,current+2];
};

onMounted(getLessons);
</script>
