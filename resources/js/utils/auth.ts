import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'

// Define las rutas que no requieren autenticación
const publicRoutes = ['/app/auth/login', '/app/auth/callback']

export const authMiddleware = (
  to: RouteLocationNormalized,
  from: RouteLocationNormalized,
  next: NavigationGuardNext
) => {
  // Verificar si existe un token en localStorage
  const token = sessionStorage.getItem('token')
  const user = sessionStorage.getItem('user')


  // Si la ruta es pública, permitir acceso sin restricciones
  if (publicRoutes.includes(to.path)) {
    // Si el usuario ya está autenticado e intenta acceder a login,
    // redirigir al dashboard
    if (token && to.path === '/app/auth/login') {
      return next('/app/')
    }
    return next()
  }

  // Para rutas protegidas, verificar si el usuario está autenticado
  if (!token) {
    // Redirigir a login si no está autenticado
    return next('/app/auth/login')
  }

  // Usuario autenticado, permitir acceso a rutas protegidas
  return next()
}

// Función para cerrar sesión
export const logout = () => {
  sessionStorage.removeItem('token')
  sessionStorage.removeItem('user')
}

// Función para verificar si el usuario está autenticado
export const user = (): AuthUser => {
  return JSON.parse(sessionStorage.getItem('user') || '{}')
}

