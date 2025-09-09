import { createApp } from 'vue'
import router from './router'
import App from './App.vue'

import '../css/main.css' // global styles

const app = createApp(App)
app.use(router)
app.mount('#app')