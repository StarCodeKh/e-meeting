<template>
  <Transition name="modal-bounce">
    <div v-if="modelValue" class="modal-overlay" @click.self="closeModal">
      
      <div class="custom-modal shadow-lg">
        
        <div class="modal-tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            class="tab-item khmer-font"
            :class="{ active: form.type === tab.id }"
            @click="form.type = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>

        <form @submit.prevent="handleSave" class="modal-inner">
          
          <div class="title-section mb-4">
            <input 
              v-model="form.title" 
              type="text" 
              placeholder="ចំណងជើង" 
              class="title-input khmer-font"
              required
            />
          </div>

          <div class="form-content">
            <div class="form-row">
              <i class="bi bi-clock icon-gray d-none d-sm-block"></i>
              <div class="pill-group">
                <input v-model="form.date" type="text" placeholder="ថ្ងៃ/ខែ" class="pill-input khmer-font flex-grow-1" />
                <input v-model="form.start_time" type="text" placeholder="8:00" class="pill-input khmer-font text-center time-field" />
                <input v-model="form.end_time" type="text" placeholder="10:00" class="pill-input khmer-font text-center time-field" />
              </div>
            </div>

            <div class="form-row">
              <i class="bi bi-person icon-gray d-none d-sm-block"></i>
              <input v-model="form.participants" type="text" placeholder="អ្នកចូលរួម" class="pill-input khmer-font w-100" />
            </div>

            <div class="form-row">
              <i class="bi bi-geo-alt icon-gray d-none d-sm-block"></i>
              <input v-model="form.location" type="text" placeholder="ទីតាំង" class="pill-input khmer-font w-100" />
            </div>

            <div class="form-row">
              <i class="bi bi-house-door icon-gray d-none d-sm-block"></i>
              <input v-model="form.room" type="text" placeholder="បន្ទប់" class="pill-input khmer-font w-100" />
            </div>

            <div class="form-row">
              <i class="bi bi-globe icon-gray d-none d-sm-block"></i>
              <input v-model="form.link" type="text" placeholder="លីងតំណភ្ជាប់" class="pill-input khmer-font w-100" />
            </div>

            <div class="color-row-container mt-3">
              <div 
                v-for="color in colorOptions" 
                :key="color.id"
                class="color-block"
                :style="{ backgroundColor: color.hex }"
                :class="{ 'selected': form.color === color.id }"
                @click="form.color = color.id"
              ></div>
            </div>
          </div>

          <div class="modal-footer-custom d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 gap-3">
            <div class="extra-info-text khmer-font text-center text-sm-start">
              ព័ត៌មានបន្ថែមផ្សេងៗ
            </div>

            <button 
              type="submit" 
              class="btn-save-gradient khmer-font w-sm-auto" 
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              រក្សាទុក
            </button>
          </div>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive } from 'vue'
import api from '@/api/axios'
import { alertStore } from '@/stores/alert'

const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue', 'refresh'])

const loading = ref(false)

const tabs = [
  { id: 'meeting', label: 'កិច្ចប្រជុំ' },
  { id: 'appointment', label: 'ការណាត់' },
  { id: 'task', label: 'ការងារ' }
]

const colorOptions = [
  { id: 'green', hex: '#9cdb9c' },
  { id: 'yellow', hex: '#fff9db' },
  { id: 'red', hex: '#e54d42' }
]

const form = reactive({
  type: 'meeting', title: '', date: '', start_time: '', end_time: '',
  participants: '', location: '', room: '', link: '', color: 'red'
})

const closeModal = () => emit('update:modelValue', false)

const handleSave = async () => {
  loading.value = true
  try {
    await api.post('/events', form)
    alertStore.show('រក្សាទុកបានជោគជ័យ', 'success')
    emit('refresh')
    closeModal()
  } catch (error) {
    alertStore.show('មានបញ្ហា', 'error')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;700&display=swap');

.khmer-font { font-family: 'Noto Sans Khmer', sans-serif; }

/* ANIMATION */
.modal-bounce-enter-active, .modal-bounce-leave-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-bounce-enter-from, .modal-bounce-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}

/* OVERLAY */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
  padding: 20px;
}

/* MODAL CARD */
.custom-modal {
  background: white;
  width: 100%;
  max-width: 580px;
  border-radius: 22px;
  position: relative;
  border: 1.5px solid #63b3ed;
  margin-top: 40px; /* Space for tabs */
}

/* RESPONSIVE TABS */
.modal-tabs {
  position: absolute;
  top: -38px;
  left: 10px;
  right: 10px;
  display: flex;
  gap: 4px;
  overflow-x: auto;
  scrollbar-width: none;
}
.modal-tabs::-webkit-scrollbar { display: none; }

.tab-item {
  background: #e54d42; color: white; border: none;
  padding: 8px 15px; border-radius: 12px 12px 0 0;
  font-size: 0.85rem; transition: 0.2s; cursor: pointer;
  white-space: nowrap;
}
.tab-item.active { background: #d43f33; font-weight: 700; padding-top: 10px; }

.modal-inner { padding: 30px 20px; }

/* INPUTS */
.title-input {
  width: 100%; border: none; border-bottom: 2.5px solid #63b3ed;
  font-size: 1.3rem; font-weight: 700; outline: none; padding-bottom: 4px;
}

.form-row { display: flex; align-items: center; gap: 15px; margin-bottom: 12px; }
.icon-gray { font-size: 1.3rem; color: #adb5bd; min-width: 25px; }

.pill-group { display: flex; gap: 8px; width: 100%; }
.pill-input {
  background: #f1f3f5; border: none; border-radius: 10px;
  padding: 10px 12px; outline: none; transition: 0.2s;
  font-size: 0.95rem;
}
.time-field { width: 80px; }

/* COLOR BLOCKS */
.color-row-container { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
.color-block {
  width: 80px; height: 35px; border-radius: 10px;
  cursor: pointer; transition: 0.2s; border: 3px solid transparent;
}
.color-block.selected { border-color: rgba(0,0,0,0.1); transform: translateY(-2px); }

/* FOOTER */
.btn-save-gradient {
  background: linear-gradient(135deg, #5da2ff 0%, #4285f4 100%);
  color: white; border: none; padding: 12px 38px;
  border-radius: 12px; font-weight: 700;
  box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
}

/* TABLET & UP */
@media (min-width: 576px) {
  .modal-inner { padding: 45px; }
  .modal-tabs { left: 20px; right: auto; overflow-x: visible; }
  .tab-item { padding: 8px 20px; font-size: 0.9rem; }
  .title-input { font-size: 1.5rem; }
  .time-field { width: 100px; }
}
</style>