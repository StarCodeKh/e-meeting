<template>
    <DashboardLayout>
        <template #header><HeaderBar /></template>
        <div class="row g-3 h-100 min-vh-100 p-2">
            <aside class="col-xl-3 col-lg-4 d-flex flex-column gap-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                    
                    <div class="mb-4 p-3 rounded-4 bg-light border border-1 shadow-sm">
                        <h1 class="display-4 fw-bold mb-0 text-dark" style="letter-spacing: -2px; font-variant-numeric: tabular-nums;">
                            {{ currentTime.split(':')[0] }}:{{ currentTime.split(':')[1] }}<span class="fs-4 align-middle text-danger ms-1">{{ currentTime.split(':')[2] }}</span>
                        </h1>
                        <div class="mt-1 text-muted small fw-semibold khmer-font">
                            {{ currentDateKhmer }}
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 text-start">
                        <div class="d-flex align-items-center p-3 rounded-4 border-start border-5 border-danger bg-danger bg-opacity-10">
                            <div class="fs-3 me-3 opacity-75"><i class="bi bi-calendar-check text-danger"></i></div>
                            <div>
                                <span class="small fw-bold d-block text-danger khmer-font">កិច្ចប្រជុំសរុប</span>
                                <h2 class="mb-0 fw-bold">{{ meetings.length }}</h2>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center p-3 rounded-4 border-start border-5 border-primary bg-primary bg-opacity-10">
                            <div class="fs-3 me-3 opacity-75"><i class="bi bi-broadcast text-primary"></i></div>
                            <div>
                                <span class="small fw-bold d-block text-primary khmer-font">កំពុងប្រជុំ</span>
                                <h2 class="mb-0 fw-bold">{{ activeMeetingsCount }}</h2>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-center gap-2">
                        <div class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></div>
                        <small class="text-muted fw-bold khmer-font">ប្រព័ន្ធដំណើរការធម្មតា</small>
                    </div>
                </div>
            </aside>

            <main class="col-xl-9 col-lg-8 col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white d-flex flex-column h-100 overflow-hidden">
                    <div class="p-4 d-flex justify-content-between align-items-center">
                        <h4 class="khmer-font fw-bold mb-0">
                            <i class="bi bi-broadcast text-danger me-2"></i>បញ្ជីកិច្ចប្រជុំថ្ងៃនេះ
                        </h4>
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Online</span>
                    </div>

                    <div class="card-body pt-0 overflow-auto flex-grow-1" style="max-height: 80vh;">
                        <transition-group name="list">
                            <div v-for="m in sortedMeetings" :key="m.id" 
                                class="mb-3 p-3 p-md-4 rounded-4 border-start border-5 bg-white shadow-sm border border-light"
                                :class="m.isActive ? 'border-danger shadow' : 'border-primary'">
                            
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-2 border-md-end text-md-center mb-2 mb-md-0">
                                        <div class="fw-bold fs-3 text-dark">{{ m.startTime }}</div>
                                        <small class="text-muted">ដល់ {{ m.endTime }}</small>
                                    </div>

                                    <div class="col-12 col-md-7 px-md-4">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge rounded-pill px-3" :class="m.isActive ? 'bg-danger' : 'bg-primary'">
                                                {{ m.statusText }}
                                            </span>
                                            <span class="badge bg-light text-dark border khmer-font fw-normal">ជាន់ទី {{ m.floor }}</span>
                                            <span class="badge bg-light text-dark border khmer-font fw-normal">បន្ទប់ {{ m.room }}</span>
                                        </div>
                                        <h4 class="khmer-font fw-bold mb-1 fs-5 fs-md-4">{{ m.title }}</h4>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ m.location }}</small>
                                    </div>

                                    <div class="col-12 col-md-3 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                                        <div class="me-3 text-md-end">
                                            <div class="fw-bold khmer-font small">{{ m.host }}</div>
                                            <div class="text-muted text-uppercase" style="font-size: 0.65rem;">ប្រធានអង្គប្រជុំ</div>
                                        </div>
                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                            <i class="bi bi-person-fill text-secondary fs-4"></i>
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

    // Data with Floor & Room
    const meetings = ref([
        { id: 1, title: 'ប្រជុំរៀបចំផែនការថវិកាឆ្នាំ ២០២៦', startTime: '08:30', endTime: '10:00', location: 'សាលាខេត្ត', floor: '២', room: '២០១', host: 'ឯកឧត្តម សាយ សំអាល់', isActive: true, statusText: 'កំពុងប្រជុំ' },
        { id: 2, title: 'សិក្ខាសាលាស្តីពីបច្ចេកវិទ្យាឌីជីថល', startTime: '10:30', endTime: '12:00', location: 'កោះពេជ្រ', floor: 'G', room: 'A1', host: 'លោក យើត វីណែល', isActive: false, statusText: 'បន្ទាប់' },
    ])

    const sortedMeetings = computed(() => [...meetings.value].sort((a, b) => b.isActive - a.isActive))
    const activeMeetingsCount = computed(() => meetings.value.filter(m => m.isActive).length)

    const updateClock = () => {
        const now = new Date()
        
        // 1. Update Time with Seconds for the countdown effect
        currentTime.value = now.toLocaleTimeString('en-GB', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        })
        
        // 2. Update Khmer Date
        currentDateKhmer.value = now.toLocaleDateString('km-KH', { 
            weekday: 'long', 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        })
    }

    onMounted(() => { updateClock(); timer = setInterval(updateClock, 1000) })
    onUnmounted(() => clearInterval(timer))
</script>
