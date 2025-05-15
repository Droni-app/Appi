<template>
  <div>
    <h1>Login</h1>
  </div>
</template>
<script setup lang="ts">
import { useRoute } from 'vue-router';
import { router } from '../../router';
const route = useRoute();

const params = new URLSearchParams(route.hash.replace('#', ''));
const idToken = params.get('id_token');

const Appi = import.meta.env.VITE_API_URL;
const SiteId = import.meta.env.VITE_APPI_SITE_ID;

const loginappi = () => {
  fetch(`${Appi}/auth/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'site': SiteId,
    },
    body: JSON.stringify({
      id_token: idToken,
    }),
  }).then((response) => {
    if (response.ok) {
      return response.json();
    } else {
      throw new Error('Error en la respuesta de la API');
    }
  }).then((data) => {
    console.log('Respuesta de la API:', data);
    if (data.token) {
      storelogin(data);
      router.push({ path: '/' });
    } else {
      console.error('No se recibió un token válido');
    }
  }).catch((error) => {
    console.error('Error al realizar la solicitud:', error);
  });
};

const storelogin = (data: any) => {
  // sotre in session storage
  sessionStorage.setItem('token', JSON.stringify(data.token));
  sessionStorage.setItem('user', JSON.stringify(data.user));
  // sotre in cookies
};

loginappi();

</script>
