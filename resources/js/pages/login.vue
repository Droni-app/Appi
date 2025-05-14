<template>
  <div class="container mx-auto max-w-md p-6">
    <div class="bg-white rounded-lg shadow-md p-8">
      <h1 class="text-2xl font-bold mb-6 text-center">Iniciar sesión</h1>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <DuiInput
          v-model="form.siteId"
          label="ID del sitio"
          placeholder="Ingrese el ID del sitio"
          :error="errors.siteId"
        />

        <DuiInput
          v-model="form.email"
          label="Correo electrónico"
          type="email"
          placeholder="correo@ejemplo.com"
          :error="errors.email"
        />

        <DuiInput
          v-model="form.password"
          label="Contraseña"
          type="password"
          placeholder="Ingrese su contraseña"
          :error="errors.password"
        />

        <div class="pt-4">
          <DuiButton
            type="submit"
            variant="solid"
            color="primary"
            :loading="loading"
            block
          >
            Iniciar sesión
          </DuiButton>
        </div>

        <div v-if="generalError" class="text-red-500 text-center mt-4">
          {{ generalError }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { DuiInput, DuiButton } from '@dronico/droni-kit'
import { login } from '../utils/auth'
import axios from 'axios'

const router = useRouter()
const loading = ref(false)
const generalError = ref('')

const form = reactive({
  siteId: '',
  email: '',
  password: ''
})

const errors = reactive({
  siteId: '',
  email: '',
  password: ''
})

const handleLogin = async () => {
  // Resetear errores
  errors.siteId = ''
  errors.email = ''
  errors.password = ''
  generalError.value = ''

  // Validación básica
  let hasError = false

  if (!form.siteId) {
    errors.siteId = 'El ID del sitio es requerido'
    hasError = true
  }

  if (!form.email) {
    errors.email = 'El correo electrónico es requerido'
    hasError = true
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Formato de correo inválido'
    hasError = true
  }

  if (!form.password) {
    errors.password = 'La contraseña es requerida'
    hasError = true
  }

  if (hasError) return

  try {
    loading.value = true

    // Hacer petición al servidor para autenticar
    const response = await axios.post('/api/auth/login', {
      site_id: form.siteId,
      email: form.email,
      password: form.password
    })

    // Si la autenticación es exitosa, guardar token y redirigir
    if (response.data && response.data.token) {
      login(response.data.token)
      router.push('/app/dashboard')
    }
  } catch (error: any) {
    console.error('Error al iniciar sesión:', error)

    // Manejar errores comunes de autenticación
    if (error.response) {
      if (error.response.status === 422) {
        // Errores de validación
        const validationErrors = error.response.data.errors || {}
        if (validationErrors.site_id) errors.siteId = validationErrors.site_id[0]
        if (validationErrors.email) errors.email = validationErrors.email[0]
        if (validationErrors.password) errors.password = validationErrors.password[0]
      } else if (error.response.status === 401) {
        // Error de credenciales
        generalError.value = 'Credenciales inválidas. Por favor, inténtalo de nuevo.'
      } else {
        generalError.value = 'Ocurrió un error durante la autenticación. Por favor, inténtalo más tarde.'
      }
    } else {
      generalError.value = 'No se pudo conectar con el servidor. Verifica tu conexión a internet.'
    }
  } finally {
    loading.value = false
  }
}
</script>
