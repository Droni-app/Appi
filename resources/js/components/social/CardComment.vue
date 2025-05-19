<template>
  <div class="bg-white p-4 rounded-lg shadow" :class="{ 'bg-gray-50': isChild }">
    <!-- Cabecera del comentario: usuario y fecha -->
    <div class="flex items-start justify-between">
      <div class="flex items-center space-x-3">
        <img
          v-if="comment.user?.avatar"
          :src="comment.user.avatar"
          class="w-10 h-10 rounded-full object-cover"
          :alt="comment.user.name"
        />
        <div v-else class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
          <i class="mdi mdi-account text-gray-500 text-xl"></i>
        </div>
        <div>
          <div class="font-medium">{{ comment.user?.name }}</div>
          <div class="text-sm text-gray-500">{{ comment.user?.email }}</div>
        </div>
      </div>
      <div class="text-sm text-gray-500">
        {{ formatDate(comment.created_at) }}
        <span v-if="isCommentEdited(comment)" class="ml-2 italic">(editado)</span>
      </div>
    </div>

    <!-- Estado de aprobación -->
    <div class="mt-2 flex justify-between items-center">
      <div
        v-if="comment.approved_at"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
      >
        <i class="mdi mdi-check-circle mr-1"></i>
        Aprobado
      </div>
      <div
        v-else
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
      >
        <i class="mdi mdi-clock-outline mr-1"></i>
        Pendiente
      </div>

      <div class="text-sm text-gray-500">
        ID: {{ comment.id }} | Tipo: {{ getFormattedCommentableType(comment.commentable_type) }} | Ref: {{ comment.commentable_id }}
      </div>
    </div>

    <!-- Contenido del comentario -->
    <div class="mt-3 prose max-w-none">
      <p>{{ comment.content }}</p>
    </div>

    <!-- Comentarios anidados (si los hay) -->
    <div v-if="comment.children && comment.children.length > 0" class="mt-4 pl-4 border-l-2 border-gray-200 space-y-3">
      <CardComment
        v-for="childComment in comment.children"
        :key="childComment.id"
        :comment="childComment"
        :site-id="siteId"
        :is-child="true"
        @comment-updated="$emit('comment-updated')"
      />
    </div>

    <!-- Acciones del comentario -->
    <div class="mt-4 flex space-x-2 justify-end">
      <DuiButton
        v-if="!comment.approved_at"
        variant="outline"
        size="sm"
        color="success"
        @click="approveComment(comment.id)"
      >
        <i class="mdi mdi-check-circle mr-1"></i>
        Aprobar
      </DuiButton>
      <DuiButton
        variant="outline"
        size="sm"
        @click="replyToComment(comment.id)"
      >
        <i class="mdi mdi-reply mr-1"></i>
        Responder
      </DuiButton>
      <DuiButton
        variant="outline"
        size="sm"
        color="danger"
        @click="deleteComment(comment.id)"
      >
        <i class="mdi mdi-delete mr-1"></i>
        Eliminar
      </DuiButton>
    </div>

    <!-- Modal de respuesta -->
    <div v-if="showReplyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Responder comentario</h3>
        <div class="space-y-4">
          <DuiTextarea v-model="replyContent" label="Respuesta" rows="4" required />
        </div>
        <div class="flex justify-end space-x-2 mt-6">
          <DuiButton type="button" variant="ghost" @click="cancelReply">Cancelar</DuiButton>
          <DuiButton type="button" color="primary" @click="submitReply">Enviar respuesta</DuiButton>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Eliminar comentario</h3>
        <p>¿Estás seguro de que quieres eliminar este comentario? Esta acción no se puede deshacer.</p>
        <div class="flex justify-end space-x-2 mt-6">
          <DuiButton type="button" variant="ghost" @click="cancelDelete">Cancelar</DuiButton>
          <DuiButton type="button" color="danger" @click="confirmDelete">Eliminar</DuiButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import appi from '@/utils/appi';
import { DuiButton, DuiTextarea } from '@dronico/droni-kit';
import { computed } from 'vue';

// Props y emisiones de eventos
const props = defineProps<{
  comment: SocialComment;
  siteId: string;
  isChild?: boolean;
}>();

const emit = defineEmits<{
  (e: 'comment-updated'): void;
}>();

// Variables reactivas
const showReplyModal = ref(false);
const showDeleteModal = ref(false);
const replyContent = ref('');
const currentCommentId = ref<number | null>(null);

// Función para formatear el tipo de comentable
const getFormattedCommentableType = (commentableType: string) => {
  return commentableType.split('\\').pop();
};

// Función para aprobar un comentario
const approveComment = async (commentId: number) => {
  try {
    await appi.put(`/social/comments/${commentId}`, {
      approved: true
    }, {
      headers: {
        site: props.siteId,
      },
    });
    emit('comment-updated');
  } catch (error) {
    console.error('Error al aprobar comentario:', error);
  }
};

// Función para preparar la respuesta a un comentario
const replyToComment = (commentId: number) => {
  currentCommentId.value = commentId;
  replyContent.value = '';
  showReplyModal.value = true;
};

// Función para enviar una respuesta
const submitReply = async () => {
  if (!replyContent.value.trim() || !currentCommentId.value) return;

  try {
    // Mapeamos el tipo de comentable al formato esperado por el controlador
    // Mapeo de tipos de modelo completos a las claves usadas en el controlador
    const commentableTypeMapping: Record<string, string> = {
      'Modules\\Content\\Models\\Post': 'content_post',
      'Modules\\CodeVs\\Models\\Challenge': 'codevs_challenge'
    };
    
    // Obtenemos la clave del comentable a partir del tipo completo
    const commentableType = props.comment.commentable_type;
    const commentable = commentableTypeMapping[commentableType];
    
    if (!commentable) {
      console.error(`Tipo de comentario no reconocido: ${commentableType}`);
      return;
    }

    // Enviamos la respuesta al endpoint correcto con todos los campos requeridos
    await appi.post(`/social/comments`, {
      commentable_id: props.comment.commentable_id,
      commentable: commentable, // Usamos la clave mapeada en lugar del tipo completo
      content: replyContent.value.trim(),
      parent_id: currentCommentId.value
    }, {
      headers: {
        site: props.siteId,
      },
    });

    showReplyModal.value = false;
    replyContent.value = '';
    currentCommentId.value = null;

    // Emitimos evento para recargar los comentarios en el componente padre
    emit('comment-updated');
  } catch (error) {
    console.error('Error al responder comentario:', error);
  }
};

// Función para cancelar la respuesta
const cancelReply = () => {
  showReplyModal.value = false;
  replyContent.value = '';
  currentCommentId.value = null;
};

// Función para preparar la eliminación de un comentario
const deleteComment = (commentId: number) => {
  currentCommentId.value = commentId;
  showDeleteModal.value = true;
};

// Función para confirmar la eliminación
const confirmDelete = async () => {
  if (!currentCommentId.value) return;

  try {
    await appi.delete(`/social/comments/${currentCommentId.value}`, {
      headers: {
        site: props.siteId,
      },
    });

    showDeleteModal.value = false;
    currentCommentId.value = null;

    // Emitimos evento para recargar los comentarios en el componente padre
    emit('comment-updated');
  } catch (error) {
    console.error('Error al eliminar comentario:', error);
  }
};

// Función para cancelar la eliminación
const cancelDelete = () => {
  showDeleteModal.value = false;
  currentCommentId.value = null;
};

// Función para formatear fechas
const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Función para verificar si un comentario ha sido editado
const isCommentEdited = (comment: SocialComment) => {
  return new Date(comment.updated_at) > new Date(comment.created_at);
};
</script>
