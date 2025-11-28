// src/main.js
import './assets/main.css' // <-- Asegúrate de que esta línea esté presente

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(router)

app.mount('#app')

