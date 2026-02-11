import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// --- នាំចូល Styles (CSS) ---
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import '../css/stylecustom.css'

// បង្កើត App Instance
const app = createApp(App)

// ប្រើប្រាស់ Router
app.use(router)

// បញ្ចូល App ទៅក្នុង DOM (#app ក្នុង index.html)
app.mount('#app')