import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

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
    path: routePath,
    component: pages[path],
  })
}

// 3. Crear el router
export const router = createRouter({
  history: createWebHistory(),
  routes,
})

