<template>
    <header class="header-bar px-3 shadow-sm sticky-top">
        <div class="header-container d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center gap-2">
                <div class="nav-square-btn shadow-sm" @click="$emit('toggle-sidebar')" role="button">
                    <i class="bi bi-list fs-5"></i>
                </div>
                <div class="d-flex align-items-center gap-2 ms-md-2">
                    <button class="nav-square-btn shadow-sm border-0 text-coral" @click="router.back()">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <button class="nav-square-btn shadow-sm border-0 text-sky" @click="router.forward()">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 bg-white-capsule p-1 shadow-sm">
                <template v-for="tab in navigationTabs" :key="tab.id">
                    <a v-if="tab.external" :href="tab.path" target="_blank" rel="noopener noreferrer" class="nav-tab-box text-decoration-none" :class="{ 'active-capsule': route.path === tab.path }">
                        <i class="bi" :class="tab.icon"></i>
                    </a>
                    <router-link v-else :to="tab.path" class="nav-tab-box text-decoration-none" active-class="active-capsule">
                        <i class="bi" :class="tab.icon"></i>
                    </router-link>
                </template>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn-create-meeting shadow-sm border-0 d-flex align-items-center px-2 px-md-3" @click="showModal = true">
                    <i class="bi bi-plus-lg fw-bold"></i>
                    <span class="ms-2 d-none d-md-inline fw-semibold khmer-font">បង្កើតកិច្ចប្រជុំថ្មី</span>
                </button>

                <router-link v-for="tab in navigationTabsSitting" :key="tab.id" :to="tab.path" class="nav-square-btn shadow-sm d-none d-sm-flex text-decoration-none" active-class="active-nav-btn">
                    <i :class="['bi', tab.icon]"></i>
                </router-link>

                <div class="position-relative">
                    <div class="nav-square-btn shadow-sm" :class="{ 'active-nav-btn': isDropdownOpen }" @click="isDropdownOpen = !isDropdownOpen" role="button">
                        <i v-if="!user.avatar" class="bi bi-person-fill"></i>
                        <img v-else :src="user.avatar" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;">
                    </div>

                    <transition name="dropdown-fade">
                        <ul v-if="isDropdownOpen" class="custom-dropdown-menu shadow border-0 rounded-4 p-2 show">
                            <li class="px-3 py-3 border-bottom mb-1 text-center">
                                <div v-if="isLoadingUser" class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                <template v-else>
                                    <div class="fw-bold text-dark khmer-font lh-1">{{ user.name || 'កំពុងទាញទិន្នន័យ...' }}</div>
                                    <small class="text-muted">{{ user.role }}</small>
                                </template>
                            </li>
                            <li>
                                <router-link to="/settings" class="dropdown-item rounded-3 khmer-font py-2" @click="isDropdownOpen = false">
                                    <i class="bi bi-person me-2"></i> កម្រងព័ត៌មាន
                                </router-link>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 text-danger khmer-font py-2 d-flex align-items-center" 
                                   @click.prevent="confirmLogout" 
                                   style="cursor: pointer;">
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
    import Swal from 'sweetalert2'
    import { ref, reactive, onMounted } from 'vue'
    import { useRouter, useRoute } from 'vue-router'
    import { UserService } from '@/services/UserService'
    import CreateEventModal from '../pages/forms/SchedulerForm.vue'

    // 1. Core Utilities
    const router = useRouter()
    const route = useRoute()

    // 2. Component States
    const showModal = ref(false)
    const isDropdownOpen = ref(false)
    const isLoggingOut = ref(false)
    const isLoadingUser = ref(true)

    // 3. Reactive Data (User Info)
    const user = reactive({ 
        name: '',
        role: '',
        avatar: null 
    })

    // 4. Navigation Config
    const navigationTabs = [
        { id: 'home', icon: 'bi-house-fill', path: '/', label: 'ទំព័រដើម' },
        { id: 'calendar', icon: 'bi-calendar3', path: '/calendar', label: 'កាលវិភាគ' },
        { id: 'meeting-monitor', icon: 'bi-display', path: '/meeting-monitor', external: true, label: 'ម៉ូនីទ័រ' },
    ]

    const navigationTabsSitting = [
        { id: 'settings', icon: 'bi-gear-fill', path: '/settings' }
    ]

    // 5. Logic: ទាញទិន្នន័យ User
    const fetchAuthUser = async () => {
        try {
            isLoadingUser.value = true
            const response = await UserService.getProfile() 
            const userData = response.data 
            user.name = userData.name
            user.role = userData.role?.name || 'User'
            user.avatar = userData.avatar_url
        } catch (error) {
            console.error('Fetch User Error:', error)
            // បើ Token ហួសសុពលភាព (401) រុញទៅ Login
            if (error.status === 401) {
                router.push('/login')
            }
        } finally {
            isLoadingUser.value = false
        }
    }

    // 6. Logic: ការចាកចេញ (Logout)
    const confirmLogout = () => {
        Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "អ្នកនឹងត្រូវចាកចេញពីប្រព័ន្ធ!",
            icon: 'warning',
            iconColor: '#d33',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-box-arrow-right me-2"></i>ចាកចេញ',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>បោះបង់',
            reverseButtons: true, 
            customClass: {
                popup: 'khmer-font rounded-4',
                confirmButton: 'rounded-3 shadow-sm',
                cancelButton: 'rounded-3 shadow-sm'
            },
            buttonsStyling: true
        }).then((result) => {
            if (result.isConfirmed) {
                handleLogout();
            }
        });
    };

    const handleLogout = async () => {
        if (isLoggingOut.value) return
        isLoggingOut.value = true
        
        try {
            await UserService.logout() 
        } catch (error) {
            console.error('Logout API failed:', error)
        } finally {
            localStorage.clear()
            router.push('/login')
            isLoggingOut.value = false
        }
    }

    // 7. Lifecycle Hook
    onMounted(() => {
        fetchAuthUser()
    })
</script>

<style scoped>
    @import "@/css/header-bar.css";
</style>