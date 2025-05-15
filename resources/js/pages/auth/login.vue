<template>
  <div class="container mx-auto px-4 py-8 text-center">
    <h1 class="text-3xl">Iniciar Sesión</h1>
    <a :href="`https://accounts.google.com/o/oauth2/v2/auth?client_id=${googleClientId}&redirect_uri=${redirectUri}&response_type=id_token token&scope=openid email profile&nonce=nonce123`" class="block mt-4">
      <DuiAction>
        <i class="mdi mdi-google"></i>
        Iniciar sesión con Google
      </DuiAction>
    </a>

  </div>
</template>

<script setup lang="ts">
import { DuiAction } from '@dronico/droni-kit';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const redirectPath = ref('/');

onMounted(() => {
  // Obtener la ruta de redirección si existe
  if (route.query.redirect && typeof route.query.redirect === 'string') {
    redirectPath.value = route.query.redirect;
  }
});

const googleClientId = import.meta.env.VITE_GOOGLE_CLIENT_ID;
const redirectUri = import.meta.env.VITE_REDIRECT_URI;
</script>
