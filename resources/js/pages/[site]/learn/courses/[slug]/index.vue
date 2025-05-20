<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <!-- Cabecera con acciones -->
      <div class="flex flex-wrap items-center justify-between mt-8 mb-6">
        <div class="flex items-center">
          <RouterLink :to="`/app/${siteId}/learn/courses`">
            <DuiAction variant="ghost" class="mr-4">
              <i class="mdi mdi-arrow-left"></i>
            </DuiAction>
          </RouterLink>
          <h1 class="text-2xl font-bold">Editar Curso</h1>
        </div>

        <div class="flex space-x-2 mt-2 sm:mt-0">
          <DuiButton
            variant="outline"
            color="danger"
            @click="deleteCourseModal = true"
          >
            <i class="mdi mdi-delete mr-2"></i>
            Eliminar
          </DuiButton>
        </div>
      </div>

      <!-- Indicador de carga inicial -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>

      <!-- Mensaje de error -->
      <div v-else-if="loadError" class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md my-4">
        <div class="flex items-center">
          <i class="mdi mdi-alert-circle mr-2 text-red-500"></i>
          <div>
            <p class="font-medium">Error al cargar el curso</p>
            <p class="mt-1">{{ loadError }}</p>
          </div>
        </div>
        <div class="mt-3">
          <DuiButton
            size="sm"
            @click="fetchCourse"
          >
            Reintentar
          </DuiButton>
        </div>
      </div>

      <!-- Formulario de edición -->
      <form v-else @submit.prevent="updateCourse" class="bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Nombre del curso -->
          <div class="col-span-1">
            <DuiInput
              v-model="course.name"
              label="Nombre del curso"
              placeholder="Escribe el nombre del curso"
              required
            />
          </div>

          <!-- Estado -->
          <div class="col-span-1">
            <DuiSelect
              v-model="course.open"
              label="Estado"
              :options="[
                { value: 1, label: 'Abierto' },
                { value: 0, label: 'Cerrado' }
              ]"
              required
            />
          </div>

          <!-- Descripción -->
          <div class="col-span-2">
            <DuiTextarea
              v-model="course.description"
              label="Descripción"
              placeholder="Describe brevemente el contenido del curso"
              rows="4"
              required
            />
          </div>

          <!-- Categoría -->
          <div class="col-span-1">
            <DuiInput
              v-model="course.category"
              label="Categoría"
              placeholder="Escribe la categoría"
            />
          </div>

          <!-- Icono -->
          <div class="col-span-1 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Icono</label>
            <AttachmentInput
              v-model="course.icon"
              placeholder="Selecciona un ícono"
              :siteId="siteId"
            />
          </div>

          <!-- Imagen principal -->
          <div class="col-span-1 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Imagen principal</label>
            <AttachmentInput
              v-model="course.picture"
              placeholder="Selecciona una imagen"
              :siteId="siteId"
            />
          </div>

          <!-- Video -->
          <div class="col-span-2">
            <DuiInput
              v-model="course.video"
              label="URL del video (opcional)"
              placeholder="Introduce la URL del video (YouTube, Vimeo, etc.)"
            />
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between mt-8 gap-2">
          <RouterLink :to="`/app/${siteId}/learn/courses`">
            <DuiAction type="button" variant="ghost">Cancelar</DuiAction>
          </RouterLink>
          <DuiButton
            type="submit"
            color="primary"
            :loading="saving"
          >
            <i class="mdi mdi-content-save mr-2"></i>
            Guardar cambios
          </DuiButton>
        </div>
      </form>

      <!-- Modal de confirmación de eliminación -->
      <div v-if="deleteCourseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
          <h3 class="text-lg font-bold mb-4">Eliminar curso</h3>
          <p>¿Estás seguro de que deseas eliminar este curso? Esta acción no se puede deshacer.</p>
          <div class="flex justify-end space-x-2 mt-6">
            <DuiButton type="button" variant="ghost" @click="deleteCourseModal = false">
              Cancelar
            </DuiButton>
            <DuiButton type="button" color="danger" :loading="deleting" @click="deleteCourse">
              Eliminar
            </DuiButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import AttachmentInput from '@/components/AttachmentInput.vue';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiAction, DuiButton, DuiSelect, DuiTextarea } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;
const slug = route.params['slug'] as string;

// Estado del curso
const course = ref<LearnCourse>({
  id: 0,
  site_id: siteId,
  user_id: '',
  category: null,
  slug: '',
  name: '',
  icon: null,
  picture: null,
  video: null,
  description: '',
  open: 1,
  created_at: '',
  updated_at: ''
});

const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const loadError = ref('');
const deleteCourseModal = ref(false);

// Cargar datos del curso
const fetchCourse = async () => {
  loading.value = true;
  loadError.value = '';

  try {
    const response = await appi.get(`/learn/courses/${slug}`, {
      headers: {
        site: siteId,
      },
    });
    course.value = response.data;
  } catch (error: any) {
    console.error('Error al cargar el curso:', error);
    loadError.value = error.response?.data?.message || 'No se pudo cargar el curso. Por favor, intenta nuevamente.';
  } finally {
    loading.value = false;
  }
};

// Actualizar el curso
const updateCourse = async () => {
  saving.value = true;

  try {
    await appi.put(`/learn/courses/${slug}`, course.value, {
      headers: {
        site: siteId,
      },
    });
    router.push(`/app/${siteId}/learn/courses`);

  } catch (error: any) {
    console.error('Error al actualizar el curso:', error);

    // Mostrar errores de validación si existen
    if (error.response?.data?.errors) {
      alert('Por favor, revisa los campos del formulario.');
    } else {
      alert('Ocurrió un error al actualizar el curso. Por favor, intenta de nuevo.');
    }
  } finally {
    saving.value = false;

  }
};

// Eliminar el curso
const deleteCourse = async () => {
  deleting.value = true;

  try {
    await appi.delete(`/learn/courses/${slug}`, {
      headers: {
        site: siteId,
      },
    });

    // Redirigir a la lista de cursos
    router.push(`/app/${siteId}/learn/courses`);
  } catch (error: any) {
    console.error('Error al eliminar el curso:', error);
    alert('No se pudo eliminar el curso. Por favor, intenta nuevamente.');
    deleteCourseModal.value = false;
  } finally {
    deleting.value = false;
  }
};

onMounted(() => {
  fetchCourse();
});
</script>
