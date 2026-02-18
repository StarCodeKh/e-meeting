<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        
        <div class="row g-4 h-100">
            <aside class="col-xl-3 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                    <div class="p-2">
                        <h5 class="khmer-font fw-bold mb-4 text-dark opacity-75 px-2">ការកំណត់ប្រព័ន្ធ</h5>
                    </div>
                    <nav class="nav flex-column gap-2">
                        <button v-for="tab in visibleTabs" 
                                :key="tab.id" 
                                @click="currentTabId = tab.id" 
                                :class="['nav-pill-custom khmer-font', { active: currentTabId === tab.id }]" 
                                type="button">
                            <div class="d-flex align-items-center gap-3">
                                <i :class="[tab.icon, 'fs-5']"></i>
                                <span>{{ tab.label }}</span>
                            </div>
                        </button>
                    </nav>
                </div>
            </aside>

            <main class="col-xl-9 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-4 p-md-5">
                    <transition name="fade-slide" mode="out-in">
                        <div v-if="activeTab" :key="activeTab.id">
                            <div class="mb-4">
                                <h4 class="khmer-font fw-bold text-primary">{{ activeTab.label }}</h4>
                                <p class="text-muted small khmer-font">សូមពិនិត្យ និងកែប្រែព័ត៌មានរបស់អ្នកនៅទីនេះ</p>
                                <hr class="opacity-10">
                            </div>
                            
                            <keep-alive>
                                <component :is="activeTab.component" />
                            </keep-alive>
                        </div>

                        <div v-else class="text-center py-5" key="no-access">
                            <p class="text-muted khmer-font">អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។</p>
                        </div>
                    </transition>
                    
                </div>
            </main>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, computed, markRaw } from 'vue'
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'
    
    // Tab Component Imports
    import ProfileTab from './tabs/ProfileTab.vue'
    import SecurityTab from './tabs/SecurityTab.vue'
    import ThemeTab from './tabs/ThemeTab.vue'
    import RoleTab from './tabs/RoleTab.vue'
    import Modules from './tabs/Modules.vue'
    import PermissionTab from './tabs/PermissionTab.vue'

    // ១. កំណត់ Role របស់ User
    const currentUserRole = ref('admin')
    const currentTabId = ref('profile')

    const ROLE_LEVELS = {
        'admin': 3,
        'manager': 2,
        'user': 1
    }

    // ៣. បញ្ជី Tab ជាមួយលក្ខខណ្ឌ Role (Standard Dynamic)
    const tabs = [
        { 
            id: 'profile', 
            label: 'ព័ត៌មានគណនី', 
            icon: 'bi bi-person-circle', 
            component: markRaw(ProfileTab),
            minRole: 'user'
        },
        { 
            id: 'security', 
            label: 'សុវត្ថិភាព', 
            icon: 'bi bi-shield-exclamation', 
            component: markRaw(SecurityTab),
            minRole: 'user'
        },
        { 
            id: 'theme', 
            label: 'ប្រភេទរចនាប័ណ្ណ', 
            icon: 'bi bi-palette', 
            component: markRaw(ThemeTab),
            minRole: 'manager'
        },
        { 
            id: 'role', 
            label: 'គ្រប់គ្រងតួនាទី',
            icon: 'bi bi-person-gear', 
            component: markRaw(RoleTab),
            minRole: 'admin'
        },
        { 
        id: 'modules', 
            label: 'គ្រប់គ្រងម៉ូឌុល', 
            icon: 'bi bi-box-seam', 
            component: markRaw(Modules),
            minRole: 'admin'
        },
        { 
            id: 'permission', 
            label: 'កំណត់សិទ្ធិប្រើប្រាស់', 
            icon: 'bi bi-key-fill', 
            component: markRaw(PermissionTab),
            minRole: 'admin'
        }
    ]

    // ៤. Logic: Filter យកតែ Tab ណាដែល User មានសិទ្ធិគ្រប់គ្រាន់ (Dynamic Permission)
    const visibleTabs = computed(() => {
        const userLevel = ROLE_LEVELS[currentUserRole.value] || 0
        return tabs.filter(tab => {
            const requiredLevel = ROLE_LEVELS[tab.minRole] || 0
            return userLevel >= requiredLevel
        })
    })

    // ៥. Logic: ស្វែងរក Tab ដែលត្រូវបង្ហាញ (Fallback ទៅ Tab ដំបូងដែលវាអាចមើលឃើញ)
    const activeTab = computed(() => {
        const found = visibleTabs.value.find(t => t.id === currentTabId.value)
        return found || (visibleTabs.value.length > 0 ? visibleTabs.value[0] : null)
    })
</script>

<style scoped>
    @import "@/css/settings-style.css";

    .fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.2s ease; }
    .fade-slide-enter-from { opacity: 0; transform: translateX(20px); }
    .fade-slide-leave-to { opacity: 0; transform: translateX(-20px); }
</style>