<template>
    <div class="app-wrapper" :class="{ 'sidebar-active': isSidebarOpen }">
        <header class="layout-header border-bottom sticky-top">
            <HeaderBar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
        </header>

        <div class="layout-container d-flex">
            <aside class="sidebar-drawer shadow-lg border-end">
                <div class="sidebar-brand p-4 d-flex align-items-center gap-3">
                    <div class="logo-box rounded-3 overflow-hidden shadow-sm">
                        <img src="@/assets/images/logo.png" alt="Logo" class="logo-img" />
                    </div>
                    <div class="brand-text">
                        <h6 class="khmer-font fw-bold text-dark mb-0">ប្រព័ន្ធប្រជុំអេឡិចត្រូនិក</h6>
                        <span class="badge bg-primary-light text-primary extra-small">Version 1.0</span>
                    </div>
                </div>

                <nav class="nav-menu flex-grow-1 p-3">
                    <div class="menu-label khmer-font text-uppercase small opacity-50 mb-3 px-2">មេនុយចម្បង</div>
                    
                    <template v-for="item in menuItems" :key="item.path">
                        <a v-if="item.external" :href="item.path" target="_blank" rel="noopener noreferrer" class="nav-link-custom mb-1 text-decoration-none" @click="autoCloseMobile">
                            <div class="icon-frame">
                                <i :class="['bi', item.icon]"></i>
                            </div>
                            <span class="khmer-font">{{ item.label }}</span>
                            <i class="bi bi-box-arrow-up-right ms-auto small opacity-25"></i>
                        </a>

                        <router-link v-else :to="item.path" class="nav-link-custom mb-1" active-class="active" @click="autoCloseMobile">
                            <div class="icon-frame">
                                <i :class="['bi', item.icon]"></i>
                            </div>
                            <span class="khmer-font">{{ item.label }}</span>
                        </router-link>
                    </template>
                </nav>

                <div class="sidebar-footer p-3 border-top">
                    <div class="user-pill d-flex align-items-center gap-2 p-2 rounded-3">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=e15b44&color=fff" class="avatar" alt="user">
                        <div class="user-meta overflow-hidden">
                            <div class="fw-bold small text-truncate">អ្នកគ្រប់គ្រង</div>
                            <div class="text-muted extra-small">admin@system.com</div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="content-view flex-grow-1">
                <div class="container-fluid p-4">
                    <slot />
                </div>
            </main>
        </div>

        <transition name="fade">
            <div v-if="isSidebarOpen" class="sidebar-backdrop" @click="isSidebarOpen = false"></div>
        </transition>
    </div>
</template>

<script setup>
    import { ref } from 'vue'
    import HeaderBar from '@/components/HeaderBar.vue'

    const isSidebarOpen = ref(false)

    const autoCloseMobile = () => {
        if (window.innerWidth < 992) isSidebarOpen.value = false
    }

    const menuItems = [
        { label: 'ផ្ទាំងគ្រប់គ្រង', icon: 'bi-house-fill', path: '/' },
        { label: 'កាលវិភាគ', icon: 'bi-calendar3', path: '/calendar' },
        
        { 
            label: 'ការតាមដានប្រព័ន្ធ', 
            icon: 'bi-display', 
            path: '/meeting-monitor', 
            external: true 
        },
        
        { label: 'ការកំណត់', icon: 'bi-gear-fill', path: '/settings' },
        { label: 'កិច្ចប្រជុំ', icon: 'bi-people-fill', path: '/meetings' },
        { label: 'គ្រប់គ្រងអ្នកប្រើប្រាស់', icon: 'bi-person-fill', path: '/users' },
        { label: 'បញ្ជីកាលវិភាគប្រជុំ', icon: 'bi bi-calendar4-event', path: '/schedules/all' },
    ]
</script>

<style scoped>
    /* Dashboard Layout Styles */
    @import "@/css/sidebar-left.css";
</style>