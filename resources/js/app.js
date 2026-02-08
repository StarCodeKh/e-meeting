import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import 'bootstrap-icons/font/bootstrap-icons.css'

import { createI18n } from 'vue-i18n'

const messages = {
  en:{
    add:"+ Add",
    video:"VIDEO CONFERENCE"
  },
  km:{
    add:"+ បន្ថែម",
    video:"កិច្ចប្រជុំវីដេអូ"
  }
}

const i18n = createI18n({
  locale:'km',
  fallbackLocale:'en',
  messages
})

createApp(App).use(router).use(i18n).mount('#app')
