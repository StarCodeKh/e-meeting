import { reactive } from 'vue'

export const alertStore = reactive({
  message: '',
  type: '',
  timeoutId: null,

  show(msg, type = 'info', duration = 3000) {
    this.message = msg
    this.type = type
    if (this.timeoutId) clearTimeout(this.timeoutId)
    this.timeoutId = setTimeout(() => {
      this.clear()
    }, duration)
  },

  clear() {
    if (this.timeoutId) clearTimeout(this.timeoutId)
    this.message = ''
    this.type = ''
    this.timeoutId = null
  },

  pause() {
    if (this.timeoutId) {
      clearTimeout(this.timeoutId)
      this.timeoutId = null
    }
  },

  resume(duration = 3000) {
    if (!this.timeoutId && this.message) {
      this.timeoutId = setTimeout(() => {
        this.clear()
      }, duration)
    }
  }
})
