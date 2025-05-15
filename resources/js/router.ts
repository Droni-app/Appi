import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { authMiddleware } from './utils/auth'

// 1. Cargar todos los archivos .vue de src/pages
const pages = import.meta.glob('/resources/js/pages/**/*.vue')

// 2. Generar rutas desde nombres de archivo
const routes: RouteRecordRaw[] = []

for (const path in pages) {
  const fileName = path
    .replace(/^.*\/pages/, '')
    .replace(/\.vue$/, '')

  const routePath = fileName.toLowerCase().endsWith('/index') ? fileName.slice(0, -6) : fileName

  routes.push({
    path: '/app' + routePath,
    component: pages[path],
  })
}

// Agregar rutas adicionales
routes.push({
  path: '/',
  redirect: '/app/auth/login'
})

// 3. Crear el router
export const router = createRouter({
  history: createWebHistory(),
  routes,
})

// 4. Agregar middleware de autenticación global
router.beforeEach(authMiddleware)
