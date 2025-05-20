<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between mt-8 mb-6">
        <div class="flex items-center">
          <RouterLink :to="`/app/${siteId}/learn/courses/${courseSlug}/lessons`">
            <DuiAction variant="ghost" class="mr-4">
              <i class="mdi mdi-arrow-left"></i>
            </DuiAction>
          </RouterLink>
          <h1 class="text-2xl font-bold">Editar Lección</h1>
        </div>
        <DuiButton variant="outline" color="danger" @click="deleteModal = true">
          <i class="mdi mdi-delete mr-2"></i> Eliminar
        </DuiButton>
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      <div v-else>
        <form @submit.prevent="updateLesson" class="bg-white p-6 rounded-lg shadow-md">
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
            <DuiTextarea v-model="lesson.content" label="Contenido" rows="6" />
            <div class="flex items-center space-x-2">
              <input type="checkbox" id="is_activity" v-model="lesson.is_activity" class="dui-checkbox mr-2 h-5 w-5 text-blue-600 focus:ring-blue-500" />
              <label for="is_activity" class="text-sm font-medium">Actividad?</label>
            </div>
            <DuiInput v-model="lesson.limit_date" label="Fecha Límite" type="date" />
          </div>
          <div class="flex justify-between mt-6 gap-2">
            <RouterLink :to="`/app/${siteId}/learn/courses/${courseSlug}/lessons`">
              <DuiAction type="button" variant="ghost">Cancelar</DuiAction>
            </RouterLink>
            <DuiButton type="submit" color="primary" :loading="saving">Guardar</DuiButton>
          </div>
        </form>

        <!-- Modal de confirmación -->
        <div v-if="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Eliminar Lección</h3>
            <p>¿Seguro que deseas eliminar esta lección?</p>
            <div class="flex justify-end mt-6 gap-2">
              <DuiButton variant="ghost" @click="deleteModal = false">Cancelar</DuiButton>
              <DuiButton color="danger" :loading="deleting" @click="deleteLesson">Eliminar</DuiButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import appi from '@/utils/appi';
import { DuiInput, DuiAction, DuiButton, DuiSelect, DuiTextarea } from '@dronico/droni-kit';

const route = useRoute();
const router = useRouter();
const siteId = route.params['site'] as string;
const courseSlug = route.params['slug'] as string;
const lessonSlug = route.params['lessonSlug'] as string;

const lesson = ref<LearnLesson>({
  id:0,course_id:0,position:0,type:'document',name:'',slug:'',video:'',live:'',live_date:'',
  description:'',content:'',is_activity:false,limit_date:'',active:true,created_at:'',updated_at:''
});
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const deleteModal = ref(false);

const typeOptions = [
  { value: 'document', label: 'Documento' },
  { value: 'video', label: 'Video' },
  { value: 'quiz', label: 'Quiz' }
];
const activeOptions = [ { value:true,label:'Sí'},{value:false,label:'No'}];
const activityOptions = activeOptions;

const fetch = async () => {
  loading.value = true;
  try {
    const { data } = await appi.get<LearnLesson>(`/learn/courses/${courseSlug}/lessons/${lessonSlug}`,{ headers:{site:siteId}});
    lesson.value=data;
  } catch(e){console.error(e);} finally{loading.value=false;}
};

const updateLesson = async () => {
  saving.value=true;
  try{
    await appi.put(`/learn/courses/${courseSlug}/lessons/${lessonSlug}`,lesson.value,{headers:{site:siteId}});
    fetch();
  } catch(e) {
    console.error(e);
  }finally{
    saving.value=false;
    router.push(`/app/${siteId}/learn/courses/${courseSlug}/lessons`);
  }
};

const deleteLesson = async ()=>{
  deleting.value=true;
  try{ await appi.delete(`/learn/courses/${courseSlug}/lessons/${lessonSlug}`,{headers:{site:siteId}});
    router.push(`/${siteId}/learn/courses/${courseSlug}/lessons`);
  }catch(e){console.error(e);}finally{deleting.value=false;deleteModal.value=false;}
};

onMounted(fetch);
</script>
