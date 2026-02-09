<template>
    <DashboardLayout>
        <template #header><HeaderBar /></template>
        <div class="row g-3 h-100">
            <aside class="col-xl-3 col-lg-4 d-flex flex-column gap-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                    <div class="mb-4 p-3 rounded-4 bg-light border shadow-sm">
                        <h1 class="display-4 fw-bold mb-0 text-dark tabular-nums">
                            {{ currentTime.split(':')[0] }}:{{ currentTime.split(':')[1] }}<span class="fs-4 align-middle text-danger ms-1">{{ currentTime.split(':')[2] }}</span>
                        </h1>
                        <div class="mt-1 text-muted small fw-semibold khmer-font">{{ currentDateKhmer }}</div>
                    </div>

                    <div class="d-flex flex-column gap-2 text-start">
                        <div class="stat-card p-3 rounded-4 border-start border-5 border-info bg-info bg-opacity-10">
                            <span class="small fw-bold d-block text-info khmer-font">កិច្ចប្រជុំសរុប</span>
                            <h2 class="mb-0 fw-bold">{{ meetings.length }}</h2>
                        </div>
                        
                        <div class="stat-card p-3 rounded-4 border-start border-5 border-danger bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="small fw-bold d-block text-danger khmer-font">កំពុងប្រជុំ</span>
                                <h2 class="mb-0 fw-bold">{{ activeMeetingsCount }}</h2>
                            </div>
                            <div v-if="activeMeetingsCount > 0" class="pulse-dot"></div>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-center gap-2">
                        <div class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></div>
                        <small class="text-muted fw-bold khmer-font">ប្រព័ន្ធដំណើរការធម្មតា</small>
                    </div>
                </div>
            </aside>

            <main class="col-xl-9 col-lg-8 col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white d-flex flex-column h-100 overflow-hidden">
                    <div class="p-4 d-flex justify-content-between align-items-center bg-white border-bottom border-light sticky-top">
                        <h4 class="khmer-font fw-bold mb-0">
                            <i class="bi bi-broadcast text-danger me-2"></i>បញ្ជីកិច្ចប្រជុំថ្ងៃនេះ
                        </h4>
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Online</span>
                    </div>

                    <div class="card-body pt-3 overflow-auto flex-grow-1 custom-scrollbar">
                        <transition-group name="list">
                            <div v-for="m in sortedMeetings" :key="m.id" 
                                class="mb-3 p-3 p-md-4 border-start border-4 rounded-3 transition-all"
                                :class="getMeetingTheme(m.status)">
                                
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-2 border-md-end text-md-center">
                                        <div class="fw-bold fs-3 text-dark lh-1">{{ m.startTime }}</div>
                                        <small class="text-muted small">ដល់ {{ m.endTime }}</small>
                                    </div>

                                    <div class="col-12 col-md-7 px-md-4">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge rounded-pill px-3 py-1 fw-bold" :class="`bg-${getStatusColor(m.status)}`">
                                                {{ m.statusText }}
                                            </span>
                                            <span class="badge bg-white text-dark border khmer-font fw-normal">ជាន់ទី {{ m.floor }}</span>
                                            <span class="badge bg-white text-dark border khmer-font fw-normal">បន្ទប់ {{ m.room }}</span>
                                        </div>
                                        <h4 class="khmer-font fw-bold mb-1 fs-5 fs-md-4 text-dark">{{ m.title }}</h4>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ m.location }}</small>
                                    </div>

                                    <div class="col-12 col-md-3 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                                        <div class="me-3 text-md-end">
                                            <div class="fw-bold khmer-font small text-dark text-truncate">{{ m.host }}</div>
                                            <div class="text-muted text-uppercase" style="font-size: 0.6rem;">ប្រធានអង្គប្រជុំ</div>
                                        </div>
                                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center shadow-sm border" style="width: 48px; height: 48px; min-width: 48px;">
                                            <i class="bi bi-person-fill fs-4" :class="`text-${getStatusColor(m.status)}`"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition-group>
                    </div>
                </div>
            </main>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, onMounted, computed, onUnmounted } from 'vue'
    import DashboardLayout from '../components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'

    const currentTime = ref(''), currentDateKhmer = ref('')
    let timer = null

    // --- Dynamic Styling Logic ---
    const THEME_MAP = {
        active: 'danger',
        completed: 'success',
        upcoming: 'warning',
        pending: 'info'
    };

    const getStatusColor = (status) => THEME_MAP[status] || 'secondary';

    const getMeetingTheme = (status) => {
        const color = getStatusColor(status);
        return `border-${color} bg-${color} bg-opacity-10 ${status === 'active' ? 'shadow-sm active-pulse' : ''}`;
    };

    // --- Data ---
    const meetings = ref([
        { id: 1, title: 'ប្រជុំរៀបចំផែនការថវិកាឆ្នាំ ២០២៦', startTime: '08:30', endTime: '10:00', location: 'សាលាខេត្ត', floor: '២', room: '២០១', host: 'ឯកឧត្តម សាយ សំអាល់', status: 'active', statusText: 'កំពុងប្រជុំ' },
        { id: 2, title: 'សិក្ខាសាលាស្តីពីបច្ចេកវិទ្យាឌីជីថល', startTime: '10:30', endTime: '12:00', location: 'កោះពេជ្រ', floor: 'G', room: 'A1', host: 'លោក យើត វីណែល', status: 'upcoming', statusText: 'បន្ទាប់' },
        { id: 3, title: 'កិច្ចប្រជុំបូកសរុបលទ្ធផលការងារ', startTime: '07:00', endTime: '08:00', location: 'ទីស្តីការគណៈរដ្ឋមន្ត្រី', floor: '១', room: '១០៥', host: 'លោកជំទាវ មីន ម៉ានី', status: 'completed', statusText: 'បានបញ្ចប់' }
    ]);

    // --- Computed Properties (Standard Dynamic Logic) ---
    const activeMeetingsCount = computed(() => meetings.value.filter(m => m.status === 'active').length);

    // Sort logic: Active first, then Upcoming, then Completed
    const sortedMeetings = computed(() => {
        const priority = { active: 1, upcoming: 2, completed: 3 };
        return [...meetings.value].sort((a, b) => (priority[a.status] || 4) - (priority[b.status] || 4));
    });

    // --- Clock Logic ---
    const updateClock = () => {
        const now = new Date();
        currentTime.value = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        currentDateKhmer.value = now.toLocaleDateString('km-KH', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    };

    onMounted(() => { updateClock(); timer = setInterval(updateClock, 1000); });
    onUnmounted(() => clearInterval(timer));
</script>

<style scoped>
    @import url('../assets/css/style.css');
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .transition-all { transition: all 0.3s ease; }

    /* Pulse for active cards */
    .active-pulse {
        animation: shadow-glow 2s infinite;
    }

    @keyframes shadow-glow {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.2); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    /* Pulse for sidebar indicator */
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #dc3545;
        border-radius: 50%;
        animation: dot-pulse 1.5s infinite;
    }

    @keyframes dot-pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(253, 13, 41, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }

    .tabular-nums { font-variant-numeric: tabular-nums; }
</style>