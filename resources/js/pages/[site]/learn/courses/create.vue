<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex items-center mt-8 mb-6">
        <RouterLink :to="`/app/${siteId}/learn/courses`">
          <DuiAction variant="ghost" class="mr-4">
            <i class="mdi mdi-arrow-left"></i>
          </DuiAction>
        </RouterLink>
        <h1 class="text-2xl font-bold">Crear Curso</h1>
      </div>

      <form @submit.prevent="saveCourse" class="bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Categoría -->
          <div class="col-span-1">
            <DuiInput
              v-model="course.category"
              label="Categoría"
              placeholder="Escribe la categoría"
            />
          </div>
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
            <DuiAction
              type="button"
              variant="ghost"
            >
              Cancelar
            </DuiAction>
          </RouterLink>
          <DuiButton
            type="submit"
            color="primary"
            :loading="saving"
          >
            <i class="mdi mdi-content-save mr-2"></i>
            Guardar curso
          </DuiButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import AttachmentInput from '@/components/AttachmentInput.vue';
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiButton, DuiSelect, DuiTextarea, DuiAction } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;

// Estado del curso
const course = ref<Partial<LearnCourse>>({
  category: '',
  name: '',
  description: '',
  open: 1,
  icon: null,
  picture: null,
  video: null
});

const saving = ref(false);

// Función para guardar el curso
const saveCourse = async () => {
  saving.value = true;

  try {
    const response = await appi.post('/learn/courses', course.value, {
      headers: {
        site: siteId,
      },
    });

    // Redirigir a la página de edición del curso recién creado
    router.push(`/app/${siteId}/learn/courses/${response.data.slug}`);
  } catch (error: any) {
    console.error('Error al guardar el curso:', error);

    // Mostrar errores de validación si existen
    if (error.response?.data?.errors) {
      alert('Por favor, revisa los campos del formulario.');
    } else {
      alert('Ocurrió un error al guardar el curso. Por favor, intenta de nuevo.');
    }
  } finally {
    saving.value = false;
  }
};
</script>
