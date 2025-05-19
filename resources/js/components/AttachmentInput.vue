<template>
  <div>
    <!-- Input con botones -->
    <div class="relative">
      <div class="flex">
      <DuiInput
        :id="id"
        v-model="inputValue"
        :placeholder="placeholder"
        :error="error"
        :disabled="disabled"
        class="grow"
      />
        <!-- Botón para seleccionar archivo existente -->
        <DuiButton
          type="button"
          @click="openLibrary"
          variant="ghost"
        >
          <i class="mdi mdi-folder-open"></i>
        </DuiButton>

        <!-- Botón para subir nuevo archivo -->
        <DuiButton
          type="button"
          variant="ghost"
          class="border-l border-gray-300 px-2 relative"
        >
          <i class="mdi mdi-cloud-upload"></i>
          <label
            :for="uploadId"
            class="absolute inset-0 cursor-pointer"
          >
            <input
              :id="uploadId"
              ref="fileInput"
              type="file"
              :accept="accept"
              @change="uploadFile"
              class="hidden"
            />
          </label>
        </DuiButton>

        <!-- Botón para abrir URL en nueva ventana -->
        <DuiButton
          v-if="inputValue"
          type="button"
          @click="openUrlInNewWindow"
          variant="ghost"
          class="border-l border-gray-300 px-2"
          title="Abrir en nueva ventana"
        >
          <i class="mdi mdi-open-in-new"></i>
        </DuiButton>
      </div>
      <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>

    <!-- Modal de biblioteca de archivos -->
    <div v-if="showLibrary" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 h-3/4 flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-800">Biblioteca de archivos</h2>
          <div class="flex space-x-2">
            <div class="relative">
              <DuiInput
                v-model="searchQuery"
                placeholder="Buscar..."
                size="sm"
                class="pr-8"
                @input="debouncedSearch"
              >
                <template #append>
                  <i class="mdi mdi-magnify"></i>
                </template>
              </DuiInput>
            </div>
            <DuiButton
              @click="closeLibrary"
              variant="ghost"
            >
              <i class="mdi mdi-close"></i>
            </DuiButton>
          </div>
        </div>

        <div class="flex-1 overflow-auto p-6">
          <div v-if="loading" class="flex justify-center items-center h-full">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
          </div>

          <div v-else-if="attachments.length === 0" class="flex flex-col items-center justify-center h-full">
            <i class="mdi mdi-file-document-outline text-6xl text-gray-400"></i>
            <p class="text-gray-500 mt-4">No hay archivos</p>
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div
              v-for="attachment in attachments"
              :key="attachment.id"
              @click="selectAttachment(attachment)"
              class="cursor-pointer border rounded-lg overflow-hidden hover:border-blue-500 transition-colors"
              :class="{ 'border-blue-500': selectedAttachment?.id === attachment.id }"
            >
              <!-- Imagen si es una imagen, icono si no es una imagen -->
              <div class="h-32 bg-gray-100 flex items-center justify-center">
                <img
                  v-if="isImage(attachment)"
                  :src="attachment.url"
                  :alt="attachment.name"
                  class="h-full w-full object-contain"
                />
                <div v-else class="text-gray-400">
                  <i class="mdi mdi-file-document-outline text-5xl"></i>
                </div>
              </div>
              <div class="p-2">
                <p class="text-xs truncate" :title="attachment.name">{{ attachment.name }}</p>
                <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="pagination && pagination.last_page && pagination.last_page > 1" class="px-6 py-3 border-t border-gray-200 flex justify-between items-center">
          <div class="flex space-x-1">
            <DuiButton
              @click="changePage((pagination.current_page || 1) - 1)"
              :disabled="(pagination.current_page || 1) === 1"
              variant="ghost"
              size="sm"
            >
              <i class="mdi mdi-chevron-left mr-1"></i> Anterior
            </DuiButton>
            <DuiButton
              v-for="page in getPageNumbers()"
              :key="page"
              @click="changePage(page)"
              :variant="(pagination.current_page || 1) === page ? 'solid' : 'ghost'"
              :color="(pagination.current_page || 1) === page ? 'primary' : 'neutral'"
              size="sm"
            >
              {{ page }}
            </DuiButton>
            <DuiButton
              @click="changePage((pagination.current_page || 1) + 1)"
              :disabled="(pagination.current_page || 1) === (pagination.last_page || 1)"
              variant="ghost"
              size="sm"
            >
              Siguiente <i class="mdi mdi-chevron-right ml-1"></i>
            </DuiButton>
          </div>
          <div class="text-sm text-gray-500">
            {{ pagination.from || 0 }}-{{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados
          </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
          <DuiButton
            @click="closeLibrary"
            variant="ghost"
            color="neutral"
            class="mr-2"
          >
            Cancelar
          </DuiButton>
          <DuiButton
            @click="confirmAttachmentSelection"
            variant="solid"
            color="primary"
            :disabled="!selectedAttachment"
          >
            Seleccionar
          </DuiButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import appi from '@/utils/appi';
import { DuiInput, DuiButton } from '@dronico/droni-kit';

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Seleccione o suba un archivo'
  },
  siteId: {
    type: String,
    required: true
  },
  accept: {
    type: String,
    default: 'image/*' // Por defecto solo imagenes
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  resize: {
    type: Object,
    default: () => ({
      width: 0,
      height: 0
    })
  }
});

// Emits
const emit = defineEmits(['update:modelValue']);

// ID únicos para los elementos
const id = `attachment-input-${Math.random().toString(36).substring(2, 9)}`;
const uploadId = `upload-${id}`;

// Estado del componente
const inputValue = ref(props.modelValue);
const fileInput = ref<HTMLInputElement | null>(null);
const loading = ref(false);
const showLibrary = ref(false);
const attachments = ref<ContentAttachment[]>([]);
const pagination = ref<Pagination<ContentAttachment[]> | null>(null);
const selectedAttachment = ref<ContentAttachment | null>(null);
const searchQuery = ref('');
const searchTimeout = ref<number | null>(null);

// Observar cambios en modelValue y actualizar inputValue
watch(() => props.modelValue, (newValue) => {
  inputValue.value = newValue;
});

// Observar cambios en inputValue y emitir eventos
watch(() => inputValue.value, (newValue) => {
  emit('update:modelValue', newValue);
});

// Función para abrir la biblioteca de archivos
const openLibrary = async () => {
  showLibrary.value = true;
  await fetchAttachments();
};

// Función para cerrar la biblioteca de archivos
const closeLibrary = () => {
  showLibrary.value = false;
  selectedAttachment.value = null;
};

// Función para confirmar la selección de un archivo
const confirmAttachmentSelection = () => {
  if (selectedAttachment.value) {
    inputValue.value = selectedAttachment.value.url;
    closeLibrary();
  }
};

// Función para seleccionar un archivo
const selectAttachment = (attachment: ContentAttachment) => {
  selectedAttachment.value = attachment;
};

// Función para obtener archivos con paginación
const fetchAttachments = async (page = 1) => {
  loading.value = true;
  try {
    const params: { page: number; search?: string; accept?: string } = { page };

    if (searchQuery.value) {
      params.search = searchQuery.value;
    }

    if (props.accept) {
      params.accept = props.accept;
    }

    const response = await appi.get<Pagination<ContentAttachment[]>>('/content/attachments', {
      params,
      headers: { site: props.siteId }
    });

    attachments.value = response.data.data as unknown as ContentAttachment[];
    pagination.value = response.data;
  } catch (error) {
    console.error('Error al cargar los archivos', error);
  } finally {
    loading.value = false;
  }
};

// Función para cambiar de página
const changePage = (page: number) => {
  if (pagination.value && page >= 1 && page <= pagination.value.last_page!) {
    fetchAttachments(page);
  }
};

// Función para generar números de página
const getPageNumbers = () => {
  if (!pagination.value) return [];

  const current = pagination.value.current_page || 1;
  const last = pagination.value.last_page || 1;

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

// Función para manejar la búsqueda con debounce
const debouncedSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = window.setTimeout(() => {
    fetchAttachments(1);
  }, 300);
};

// Función para subir un archivo
const uploadFile = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || target.files.length === 0) return;

  const file = target.files[0];

  // Crear FormData para la subida
  const formData = new FormData();
  formData.append('file', file);

  // Agregar dimensiones si se especifican
  if (props.resize.width && props.resize.height) {
    formData.append('width', props.resize.width.toString());
    formData.append('height', props.resize.height.toString());
  }

  loading.value = true;

  try {
    const response = await appi.post<ContentAttachment>('/content/attachments', formData, {
      headers: {
        site: props.siteId,
        'Content-Type': 'multipart/form-data'
      }
    });

    inputValue.value = response.data.url;

    // Limpiar input file
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  } catch (error: any) {
    console.error('Error al subir el archivo', error);
    alert(error.response?.data?.message || 'Error al subir el archivo');
  } finally {
    loading.value = false;
  }
};

// Función para verificar si es una imagen
const isImage = (attachment: ContentAttachment) => {
  return attachment.mime_type.startsWith('image/');
};

// Función para formatear el tamaño del archivo
const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Función para abrir la URL en una nueva ventana
const openUrlInNewWindow = () => {
  if (inputValue.value) {
    window.open(inputValue.value, '_blank');
  }
};
</script>
