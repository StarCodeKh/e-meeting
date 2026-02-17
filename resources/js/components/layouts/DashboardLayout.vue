
<template>
    <div class="app-wrapper" :class="{ 'sidebar-active': isSidebarOpen }">
        <HeaderBar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
        <div class="layout-container d-flex">
            <aside class="sidebar-drawer shadow-lg border-end">
                <div class="sidebar-brand p-4 d-flex align-items-center gap-3">
                    <div class="logo-box rounded-3 overflow-hidden shadow-sm">
                        <img src="@/assets/images/logo.png" alt="Logo" class="logo-img" />
                    </div>
                    <div class="brand-text">
                        <h6 class="khmer-font fw-bold text-dark mb-0">ប្រព័ន្ធប្រជុំអេឡិចត្រូនិក</h6>
                    </div>
                </div>

               <nav class="nav-menu flex-grow-1 p-3 overflow-auto custom-scrollbar">
                    <div class="menu-label khmer-font text-uppercase small opacity-50 mb-3 px-2">មេនុយចម្បង</div>
                    <template v-if="loading">
                        <div v-for="n in 6" :key="n" class="placeholder-glow mb-2 px-2">
                            <div class="placeholder col-12 rounded-3 py-3 bg-secondary opacity-10"></div>
                        </div>
                    </template>

                    <template v-else-if="menuItems.length > 0">
                        <router-link v-for="item in menuItems" :key="item.id" :to="item.path" class="nav-link-custom mb-1 text-decoration-none" active-class="active" @click="autoCloseMobile">
                            <div class="icon-frame"><i :class="['bi', item.icon || 'bi-grid-fill']"></i></div>
                            <span class="khmer-font">{{ item.label }}</span>
                        </router-link>
                    </template>

                    <div v-else class="text-center py-5 opacity-50">
                        <i class="bi bi-folder2-open d-block fs-2"></i>
                        <span class="khmer-font small">មិនទាន់មានមេនុយ</span>
                    </div>
                </nav>
            </aside>

            <main class="content-view flex-grow-1">
                <div class="container-fluid p-4">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue'
    import ModuleService from '@/services/ModuleService'
    import { UserService } from '@/services/UserService'
    import HeaderBar from '@/components/HeaderBar.vue'

    const isSidebarOpen = ref(false)
    const rawMenus = ref([])
    const authUser = ref(null)
    const loading = ref(true)

    /**
     * ១. មុខងារទាញទិន្នន័យតាមបែប Standard (Parallel Fetching)
     */
    const initDashboard = async () => {
        loading.value = true
        try {
            const [menuRes, userRes] = await Promise.all([
                ModuleService.getAll({ is_menu: 1 }),
                UserService.getProfile()
            ])

            rawMenus.value = menuRes?.data?.data || menuRes?.data || menuRes || []
            
            const userData = userRes?.data || userRes
            authUser.value = Array.isArray(userData) ? userData[0] : userData

        } catch (error) {
            console.error("[Dashboard Error]:", error)
        } finally {
            loading.value = false
        }
    }

    /**
     * ២. Centralized Authorization Logic
     * ងាយស្រួលកែប្រែ Rule ឆែកសិទ្ធិនៅកន្លែងតែមួយ
     */
    const hasAccess = (item) => {
        if (Number(item.is_active) !== 1) return false
        const userRole = authUser.value?.role?.toUpperCase()
        if (userRole === 'ADMIN') return true

        if (!item.permission_name) return true

        return authUser.value?.permissions?.includes(item.permission_name)
    }

    /**
     * ៣. Dynamic Computed Menu
     */
    const menuItems = computed(() => {
        if (!authUser.value || !Array.isArray(rawMenus.value)) return []

        return rawMenus.value.filter(hasAccess).sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
    })

    onMounted(initDashboard)

    const autoCloseMobile = () => {
        if (window.innerWidth < 992) isSidebarOpen.value = false
    }
    
</script>

<style scoped>
    /* Dashboard Layout Styles */
    @import "@/css/sidebar-left.css";
</style>