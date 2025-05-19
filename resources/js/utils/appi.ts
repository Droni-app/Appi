// src/lib/axios.js
import axios from 'axios'

const appi = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
})

// Interceptor de solicitud (request)
appi.interceptors.request.use(
  config => {
    // Agregar token, por ejemplo
    const token = sessionStorage.getItem('token')
    if (token) {
      const accessToken = JSON.parse(String(token))
      config.headers.Authorization = `Bearer ${accessToken.plainTextToken}`
    }
    return config
  },
  error => Promise.reject(error)
)

// Interceptor de respuesta (response)
appi.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Manejo global de errores 401 (no autorizado)
      console.error('No autorizado')
    }
    return Promise.reject(error)
  }
)

export default appi
