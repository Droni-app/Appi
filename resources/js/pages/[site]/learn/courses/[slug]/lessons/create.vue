<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex items-center mt-8 mb-6">
        <RouterLink :to="`/app/${siteId}/learn/courses/${courseSlug}/lessons`">
          <DuiAction variant="ghost" class="mr-4">
            <i class="mdi mdi-arrow-left"></i>
          </DuiAction>
        </RouterLink>
        <h1 class="text-2xl font-bold">Crear Lección</h1>
      </div>

      <form @submit.prevent="saveLesson" class="bg-white p-6 rounded-lg shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <DuiInput v-model="lesson.name" label="Nombre" required />
          <DuiInput v-model="lesson.position" label="# Posición" type="number" />
          <DuiSelect v-model="lesson.type" label="Tipo" :options="typeOptions" required />
          <div class="flex items-center space-x-2">
            <input type="checkbox" id="active" v-model="lesson.active" class="dui-checkbox mr-2 h-5 w-5 text-blue-600 focus:ring-blue-500" />
            <label for="active" class="text-sm font-medium">Activo</label>
          </div>
          <DuiInput v-model="lesson.video" label="URL Video" placeholder="https://..." />
          <DuiInput v-model="lesson.live" label="URL Live (opcional)" placeholder="https://..." />
          <DuiInput v-model="lesson.live_date" label="Fecha Live" type="date" />
          <DuiTextarea v-model="lesson.description" label="Descripción" rows="3" />
          <div class="col-span-2">
            <DuiTextarea v-model="lesson.content" label="Contenido" />
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <input type="checkbox" id="is_activity" v-model="lesson.is_activity" class="dui-checkbox mr-2 h-5 w-5 text-blue-600 focus:ring-blue-500" />
              <label for="is_activity" class="text-sm font-medium">Actividad?</label>
            </div>
            <DuiInput
              v-model="lesson.limit_date"
              label="Fecha Límite"
              type="date"
              :disabled="!lesson.is_activity"
            />
          </div>

        </div>
        <div class="flex justify-between mt-6 gap-2">
          <RouterLink :to="`/app/${siteId}/learn/courses/${courseSlug}/lessons`">
            <DuiAction type="button" variant="ghost">Cancelar</DuiAction>
          </RouterLink>
          <DuiButton type="submit" color="primary" :loading="saving">Guardar</DuiButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiAction, DuiButton, DuiSelect, DuiTextarea } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;
const courseSlug = route.params['slug'] as string;

const lesson = ref<Partial<LearnLesson>>({
  name: '',
  position: 0,
  type: 'document',
  active: true,
  video: '',
  live: '',
  live_date: '',
  description: '',
  content: '',
  is_activity: false,
  limit_date: ''
});
const saving = ref(false);

const typeOptions = [
  { value: 'document', label: 'Documento' },
  { value: 'video', label: 'Video' },
  { value: 'quiz', label: 'Quiz' }
];
const activeOptions = [
  { value: true, label: 'Sí' },
  { value: false, label: 'No' }
];
const activityOptions = activeOptions;

const saveLesson = async () => {
  saving.value = true;
  try {
    const response = await appi.post(`/learn/courses/${courseSlug}/lessons`, lesson.value, { headers:{ site: siteId }});
    router.push(`/app/${siteId}/learn/courses/${courseSlug}/lessons/${response.data.slug}`);
  } catch(e)
  {
    console.error(e); alert('Error al crear lección');
  } finally {
    saving.value = false;
    router.push(`/app/${siteId}/learn/courses/${courseSlug}/lessons`);
  }
};
</script>
