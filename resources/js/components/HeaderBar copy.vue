<template>
  <header class="header-bar px-3 shadow-sm">
    <div class="header-container d-flex align-items-center h-100">
      
      <div class="header-section section-left d-flex align-items-center gap-3 gap-md-4">
        <div class="menu-btn shadow-sm" @click="$emit('toggle-sidebar')" role="button">
          <i class="bi bi-list"></i>
        </div>
        
        <div class="nav-arrows d-none d-md-flex align-items-center gap-4">
          <div class="arrow-icon text-coral" role="button"><i class="bi bi-arrow-left"></i></div>
          <div class="arrow-icon text-sky" role="button"><i class="bi bi-arrow-right"></i></div>
        </div>
      </div>

      <div class="header-section section-center d-flex justify-content-center">
        <div class="center-nav d-flex align-items-center gap-3">
          <div 
            class="nav-item-box" 
            :class="{ active: activeTab === 'home' }" 
            @click="activeTab = 'home'"
          >
            <i class="bi bi-house"></i>
          </div>

          <div 
            class="nav-item-box active-squircle" 
            :class="{ active: activeTab === 'calendar' }" 
            @click="activeTab = 'calendar'"
          >
            <i class="bi bi-calendar3"></i>
          </div>

          <div 
            class="nav-item-box d-none d-sm-flex" 
            :class="{ active: activeTab === 'display' }" 
            @click="activeTab = 'display'"
          >
            <i class="bi bi-display"></i>
          </div>
        </div>
      </div>

      <div class="header-section section-right d-flex align-items-center justify-content-end gap-3">
        
        <button  class="btn-create-gradient shadow-sm" @click="showModal = true">
            <i class="bi bi-plus-lg"></i>
            <span class="ms-2 d-none d-lg-inline">{{ createLabel }}</span>
        </button>

        <CreateEventModal v-model="showModal" />
        
        <div class="action-icons d-none d-md-flex align-items-center gap-3 ms-2">
          <div class="icon-util"><i class="bi bi-envelope"></i></div>
          <div class="icon-util"><i class="bi bi-gear"></i></div>
        </div>

        <div class="dropdown profile-dropdown">
          <div 
            class="profile-trigger dropdown-toggle" 
            id="profileDropdown" 
            data-bs-toggle="dropdown" 
            data-bs-display="static"
            aria-expanded="false"
            role="button"
          >
            <i class="bi bi-person"></i>
          </div>

          <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 mt-2 smooth-menu">
            <li class="px-3 py-2 border-bottom mb-1">
              <div class="fw-bold khmer-font text-dark">{{ user.name }}</div>
              <small class="text-muted">{{ user.role }}</small>
            </li>
            <li><a class="dropdown-item rounded khmer-font" href="#"><i class="bi bi-person me-2"></i> កម្រងព័ត៌មាន</a></li>
            <li><a class="dropdown-item rounded khmer-font" href="#"><i class="bi bi-shield-lock me-2"></i> សុវត្ថិភាព</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
             <a 
                class="dropdown-item rounded text-danger khmer-font d-flex align-items-center" 
                href="javascript:void(0)" 
                @click="handleLogout"
                :class="{ 'disabled-link': isLoggingOut }"
            >
                <i v-if="!isLoggingOut" class="bi bi-box-arrow-right me-2"></i>
                <span v-else class="spinner-border spinner-border-sm me-2" role="status"></span>
                
                <span>ចាកចេញ</span>
            </a>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </header>
</template>

<script setup>
    import { ref, reactive } from 'vue'
    import { useRouter } from 'vue-router'

    import CreateEventModal from '../pages/forms/SchedulerForm.vue'
    const showModal = ref(false)


    // 1. Setup Router and Emits
    const router = useRouter()
    const emit = defineEmits(['toggle-sidebar'])

    // 2. UI State
    const createLabel = ref('បង្កើតកិច្ចប្រជុំថ្មី')
    const activeTab = ref('calendar')
    const isLoggingOut = ref(false)

    // 3. User Data
    const user = reactive({
        name: 'យើត វីណែល',
        role: 'Administrator'
    })

    /**
     * Handle Logout (Dynamic & Standard)
     * This handles the API call, session clearing, and redirection.
     */
    const handleLogout = async () => {
        if (isLoggingOut.value) return

        try {
            isLoggingOut.value = true
            await new Promise(resolve => setTimeout(resolve, 800)) 
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            sessionStorage.clear()
            router.push('/login')

        } catch (error) {
            console.error('Logout Error:', error)
        } finally {
            isLoggingOut.value = false
        }
    }
</script>

<style scoped>
    /* --- FONT & CORE LAYOUT --- */
    .khmer-font { font-family: 'Noto Sans Khmer', sans-serif; }

    header {
    font-family: 'Noto Sans Khmer', sans-serif;
    height: 75px;
    background: #ffffff;
    border-bottom: 1px solid #eef2f6;
    position: sticky;
    top: 0;
    z-index: 1000;
    }

    .header-container { width: 100%; }
    .header-section { flex: 1; min-width: 0; }
    .section-center { flex: 0 0 auto; }

    /* --- BUTTONS & INTERACTIVE ELEMENTS --- */
    .menu-btn {
    width: 48px;
    height: 48px;
    background: #ffffff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #8b96a5;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .menu-btn:active { transform: scale(0.9); background: #f1f5f9; }

    .arrow-icon { font-size: 1.6rem; cursor: pointer; transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .arrow-icon:hover { transform: scale(1.2); }
    .text-coral { color: #eb7c60; }
    .text-sky { color: #63b3ed; }

    /* --- NAV ITEMS: THE BOUNCY SQUIRCLE --- */
    .nav-item-box {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.9rem;
    color: #555;
    cursor: pointer;
    border-radius: 16px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); /* Bounce Curve */
    }

    .active-squircle.active {
    background-color: #e54d42;
    color: #ffffff;
    box-shadow: 0 8px 16px rgba(229, 77, 66, 0.3);
    transform: scale(1.05);
    }

    /* --- CREATE BUTTON --- */
    .btn-create-gradient {
    background: linear-gradient(135deg, #4285f4 0%, #63b3ed 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    }
    .btn-create-gradient:hover { filter: brightness(1.05); transform: translateY(-1px); }
    .btn-create-gradient:active { transform: scale(0.96); }

    /* --- ULTRA SMOOTH DROPDOWN FIX --- */
    .dropdown-toggle::after { display: none; }

    .smooth-menu {
    display: block !important; /* Standard fix to allow transitions */
    visibility: hidden;
    opacity: 0;
    transform: translateY(12px) scale(0.95);
    transform-origin: top right;
    transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.165, 0.84, 0.44, 1), visibility 0.25s;
    pointer-events: none;
    min-width: 200px;
    }

    .show.smooth-menu {
    visibility: visible;
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
    }

    .dropdown-item {
    padding: 10px 15px;
    transition: background 0.2s ease, padding-left 0.2s ease;
    }
    .dropdown-item:hover {
    background-color: #f8fafc;
    padding-left: 18px;
    }

    .icon-util, .profile-trigger {
    font-size: 1.8rem;
    color: #555;
    cursor: pointer;
    transition: color 0.2s;
    }
    .icon-util:hover, .profile-trigger:hover { color: #4285f4; }

    /* Responsive Adjustments */
    @media (max-width: 576px) {
    header { height: 65px; }
    .nav-item-box { width: 45px; height: 45px; font-size: 1.5rem; }
    }
</style>