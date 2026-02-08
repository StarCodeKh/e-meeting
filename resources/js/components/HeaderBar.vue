<template>
  <header class="header-bar px-3 shadow-sm sticky-top">
    <div class="header-container d-flex align-items-center justify-content-between h-100">
      
      <div class="d-flex align-items-center gap-2">
        <div class="nav-square-btn shadow-sm" @click="$emit('toggle-sidebar')" role="button">
          <i class="bi bi-list fs-5"></i>
        </div>
        <div class="d-flex align-items-center gap-2 ms-md-2">
          <div class="nav-square-btn shadow-sm text-coral" role="button"><i class="bi bi-arrow-left"></i></div>
          <div class="nav-square-btn shadow-sm text-sky" role="button"><i class="bi bi-arrow-right"></i></div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2 bg-white-capsule p-1 shadow-sm">
        <div class="nav-tab-box" :class="{ 'active-home': activeTab === 'home' }" @click="activeTab = 'home'">
          <i class="bi bi-house-fill"></i>
        </div>
        <div class="nav-tab-box" :class="{ 'active-tab': activeTab === 'calendar' }" @click="activeTab = 'calendar'">
          <i class="bi bi-calendar3"></i>
        </div>
        <div class="nav-tab-box" :class="{ 'active-tab': activeTab === 'display' }" @click="activeTab = 'display'">
          <i class="bi bi-display"></i>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button class="btn-create-meeting shadow-sm border-0 d-flex align-items-center px-2 px-md-3" @click="showModal = true">
            <i class="bi bi-plus-lg fw-bold"></i>
            <span class="ms-2 d-none d-md-inline fw-semibold khmer-font">បង្កើតកិច្ចប្រជុំថ្មី</span>
        </button>

        <div class="nav-square-btn shadow-sm d-none d-sm-flex" role="button">
          <i class="bi bi-gear-fill"></i>
        </div>

        <div class="position-relative">
          <div 
            class="nav-square-btn shadow-sm" 
            @click="isDropdownOpen = !isDropdownOpen"
            role="button"
          >
            <i class="bi bi-person-fill"></i>
          </div>

          <transition name="dropdown-fade">
            <ul v-if="isDropdownOpen" class="custom-dropdown-menu shadow border-0 rounded-4 p-2">
              <li class="px-3 py-2 border-bottom mb-1">
                <div class="fw-bold text-dark khmer-font lh-1">{{ user.name }}</div>
                <small class="text-muted small-text">Administrator</small>
              </li>
              <li><a class="dropdown-item rounded-3 khmer-font py-2" href="#"><i class="bi bi-person me-2"></i> កម្រងព័ត៌មាន</a></li>
              <li><a class="dropdown-item rounded-3 khmer-font py-2" href="#"><i class="bi bi-shield-lock me-2"></i> សុវត្ថិភាព</a></li>
              <li><hr class="dropdown-divider opacity-50"></li>
              <li>
                <a class="dropdown-item rounded-3 text-danger khmer-font py-2 d-flex align-items-center" href="#" @click.prevent="handleLogout">
                  <span v-if="isLoggingOut" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-box-arrow-right me-2"></i>
                  <span>ចាកចេញ</span>
                </a>
              </li>
            </ul>
          </transition>
        </div>
      </div>
    </div>
    <CreateEventModal v-model="showModal" />
    
    <div v-if="isDropdownOpen" class="dropdown-backdrop" @click="isDropdownOpen = false"></div>
  </header>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import CreateEventModal from '../pages/forms/SchedulerForm.vue'

const router = useRouter()
const activeTab = ref('calendar')
const showModal = ref(false)
const isDropdownOpen = ref(false)
const isLoggingOut = ref(false)
const user = reactive({ name: 'យើត វីណែល' })

const handleLogout = async () => {
  if (isLoggingOut.value) return;
  isLoggingOut.value = true;
  setTimeout(() => {
    localStorage.clear();
    router ? router.push('/login') : window.location.href = '/login';
  }, 800);
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&display=swap');

.khmer-font { font-family: 'Kantumruy Pro', sans-serif !important; }
.header-bar { height: 72px; background-color: #f0f2f5; border-bottom: 1px solid #dee2e6; z-index: 1050; }

/* Squircle Buttons */
.nav-square-btn {
  width: 42px; height: 42px;
  display: flex; align-items: center; justify-content: center;
  background-color: #ffffff; border-radius: 12px;
  cursor: pointer; color: #5f6368;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.nav-square-btn:hover { background-color: #fcfcfc; color: #333; transform: scale(1.02); }

/* Center Capsule */
.bg-white-capsule { background-color: #ffffff; border-radius: 14px; }
.nav-tab-box { 
  width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; 
  border-radius: 10px; cursor: pointer; transition: 0.25s ease;
}
.active-home { background-color: #e76f51 !important; color: white !important; }
.active-tab { background-color: #f8f9fa; border: 1px solid #dee2e6; color: #202124; }

/* Create Button */
.btn-create-meeting { 
  background-color: #3498db; color: white; border-radius: 12px; 
  height: 42px; transition: 0.2s; 
}
.btn-create-meeting:hover { filter: brightness(1.05); box-shadow: 0 4px 10px rgba(52, 152, 219, 0.2); }

/* --- THE SMOOTH DROPDOWN --- */
.custom-dropdown-menu {
  position: absolute;
  top: 100%; right: 0;
  margin-top: 10px;
  min-width: 210px;
  background: white;
  z-index: 1100;
  list-style: none;
}

/* Vue Transition Logic */
.dropdown-fade-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); /* Bouncy smooth */
}
.dropdown-fade-leave-active {
  transition: all 0.2s ease-in;
}
.dropdown-fade-enter-from, .dropdown-fade-leave-to {
  transform: translateY(15px) scale(0.95);
  opacity: 0;
}

.dropdown-item { transition: 0.2s; cursor: pointer; }
.dropdown-item:hover { background-color: #f1f3f5; }
.dropdown-backdrop { position: fixed; inset: 0; z-index: 1090; }

.text-coral { color: #e76f51; }
.text-sky { color: #3498db; }
.small-text { font-size: 0.75rem; }

@media (max-width: 576px) {
  .header-bar { height: 65px; }
  .nav-square-btn, .nav-tab-box { width: 36px; height: 36px; }
}
</style>