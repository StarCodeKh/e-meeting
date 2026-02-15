import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Global Styles
import './style.css' 
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

const app = createApp(App)

// Global Router Guard for Titles
router.beforeEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} | My App` : 'My App'
})

app.use(createPinia())
app.use(router)
app.mount('#app')