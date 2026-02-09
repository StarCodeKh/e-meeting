<template>
    <header class="header-bar px-3 shadow-sm sticky-top">
        <div class="header-container d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-2">
                <div class="nav-square-btn shadow-sm" @click="$emit('toggle-sidebar')" role="button">
                    <i class="bi bi-list fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-2 ms-md-2">
                    <button class="nav-square-btn shadow-sm border-0 text-coral" @click="$router.back()">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <button class="nav-square-btn shadow-sm border-0 text-sky" @click="$router.forward()">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 bg-white-capsule p-1 shadow-sm">
                <router-link 
                    v-for="tab in navigationTabs" 
                    :key="tab.id"
                    :to="tab.path"
                    class="nav-tab-box text-decoration-none"
                    active-class="active-capsule"
                >
                    <i class="bi" :class="tab.icon"></i>
                </router-link>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn-create-meeting shadow-sm border-0 d-flex align-items-center px-2 px-md-3" @click="showModal = true">
                    <i class="bi bi-plus-lg fw-bold"></i>
                    <span class="ms-2 d-none d-md-inline fw-semibold khmer-font">បង្កើតកិច្ចប្រជុំថ្មី</span>
                </button>

                <router-link 
                    v-for="tab in navigationTabsSitting" 
                    :key="tab.id"
                    :to="tab.path"
                    class="nav-square-btn shadow-sm d-none d-sm-flex text-decoration-none"
                    active-class="active-nav-btn"
                >
                    <i :class="['bi', tab.icon]"></i>
                </router-link>

                <div class="position-relative">
                    <div class="nav-square-btn shadow-sm" :class="{ 'active-nav-btn': isDropdownOpen }" @click="isDropdownOpen = !isDropdownOpen" role="button">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <transition name="dropdown-fade">
                        <ul v-if="isDropdownOpen" class="custom-dropdown-menu shadow border-0 rounded-4 p-2 show">
                            <li class="px-3 py-2 border-bottom mb-1 text-center">
                                <div class="fw-bold text-dark khmer-font lh-1">{{ user.name }}</div>
                                <small class="text-muted">Administrator</small>
                            </li>
                            <li>
                                <router-link to="/settings" class="dropdown-item rounded-3 khmer-font py-2" @click="isDropdownOpen = false">
                                    <i class="bi bi-person me-2"></i> កម្រងព័ត៌មាន
                                </router-link>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 text-danger khmer-font py-2 d-flex align-items-center" @click.prevent="handleLogout">
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
    import { ref, reactive, computed } from 'vue'
    import { useRouter, useRoute } from 'vue-router'
    import CreateEventModal from '../pages/forms/SchedulerForm.vue'

    // 1. Core Utilities
    const router = useRouter()
    const route = useRoute()

    // 2. Component States
    const showModal = ref(false)
    const isDropdownOpen = ref(false)
    const isLoggingOut = ref(false)

    // 3. Reactive Data
    const user = reactive({ 
        name: 'យើត វីណែល',
        role: 'Administrator',
        avatar: null 
    })

    const navigationTabs = [
        { id: 'home', icon: 'bi-house-fill', path: '/' },
        { id: 'calendar', icon: 'bi-calendar3', path: '/calendar' },
        { id: 'meeting-monitor', icon: 'bi-display', path: '/meeting-monitor' },
    ]

    const navigationTabsSitting = [
        { id: 'settings', icon: 'bi-gear-fill', path: '/settings' }
    ]

    const handleLogout = async () => {
        if (isLoggingOut.value) return
        
        isLoggingOut.value = true
        
        try {
            // In a real app, you would call: await api.logout()
            await new Promise(resolve => setTimeout(resolve, 1000))
            
            localStorage.clear()
            router.push('/login')
        } catch (error) {
            console.error('Logout failed:', error)
            isLoggingOut.value = false
        }
    }
    const currentPath = computed(() => route.path)
</script>

<style scoped>
    @import "@/css/header-bar.css";
</style>