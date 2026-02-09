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
                    <div class="nav flex-column gap-2">
                        <button 
                            v-for="tab in tabs" :key="tab.id"
                            @click="currentTab = tab.id"
                            :class="['nav-pill-custom khmer-font', { active: currentTab === tab.id }]"
                        >
                            <div class="d-flex align-items-center gap-3">
                                <i :class="[tab.icon, 'fs-5']"></i>
                                <span>{{ tab.label }}</span>
                            </div>
                        </button>
                    </div>
                </div>
            </aside>

            <main class="col-xl-9 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-4 p-md-5">
                    <transition name="fade-slide" mode="out-in">
                        <div :key="currentTab">
                            <div class="mb-4">
                                <h4 class="khmer-font fw-bold text-primary">{{ activeTabData.label }}</h4>
                                <p class="text-muted small khmer-font">សូមពិនិត្យ និងកែប្រែព័ត៌មានរបស់អ្នកនៅទីនេះ</p>
                                <hr class="opacity-10">
                            </div>
                            <component :is="activeTabData.component" />
                        </div>
                    </transition>
                </div>
            </main>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, computed } from 'vue'
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'
    import ProfileTab from './tabs/ProfileTab.vue'
    import SecurityTab from './tabs/SecurityTab.vue'
    import ThemeTab from './tabs/ThemeTab.vue'

    const currentTab = ref('profile')
    const tabs = [
        { id: 'profile', label: 'ព័ត៌មានគណនី', icon: 'bi bi-person-circle', component: ProfileTab },
        { id: 'security', label: 'សុវត្ថិភាព', icon: 'bi bi-shield-lock', component: SecurityTab },
        { id: 'theme', label: 'ប្រភេទរចនាប័ណ្ណ', icon: 'bi bi-palette', component: ThemeTab }
    ]

    const activeTabData = computed(() => tabs.find(t => t.id === currentTab.value))
</script>

<style scoped>
    @import "@/css/settings-style.css";
    .fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.2s ease; }
    .fade-slide-enter-from { opacity: 0; transform: translateX(10px); }
    .fade-slide-leave-to { opacity: 0; transform: translateX(-10px); }
</style>