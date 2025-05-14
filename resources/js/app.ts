import { createApp } from 'vue'
import App from './App.vue'
import { router } from './router'

// Importar la hoja de estilos de droni-kit
import '@dronico/droni-kit/dist/droni-kit.css'

const app = createApp(App)

app.use(router).mount('#app')
