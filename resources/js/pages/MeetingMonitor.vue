<template>
    <DashboardLayout>
        <template #header><HeaderBar /></template>
        <div class="monitor">
            <div class="row g-3 h-100">
                
                <aside class="col-xl-3 col-lg-4 d-flex flex-column gap-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                        <div class="mb-4 clock-box">
                            <h1 class="display-4 fw-bold mb-0 text-dark tabular-nums">
                                {{ currentTime.split(':')[0] }}:{{ currentTime.split(':')[1] }}
                                <span class="seconds-tick">{{ currentTime.split(':')[2] }}</span>
                            </h1>
                            <div class="khmer-font text-muted mt-2 fade-in-up">
                                {{ currentDateKhmer }}
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="stat-pill coral">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="khmer-font">កិច្ចប្រជុំសរុប</span>
                                    <h2 class="mb-0 fw-bold">{{ meetings.length }}</h2>
                                </div>
                            </div>
                            <div class="stat-pill sky">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="khmer-font">កំពុងប្រជុំ</span>
                                    <h2 class="mb-0 fw-bold">{{ activeMeetingsCount }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-center gap-2">
                            <span class="dot-pulse"></span>
                            <small class="khmer-font text-muted">ប្រព័ន្ធដំណើរការធម្មតា</small>
                        </div>
                    </div>
                </aside>

                <main class="col-xl-9 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100 d-flex flex-column">
                        <div class="p-4 d-flex justify-content-between align-items-center">
                            <h4 class="khmer-font fw-bold mb-0">
                                <i class="bi bi-broadcast text-danger me-2"></i>បញ្ជីកិច្ចប្រជុំថ្ងៃនេះ
                            </h4>
                            <span class="badge rounded-pill bg-light text-success border px-3 py-2">Online</span>
                        </div>

                        <div class="card-body pt-0 custom-scrollbar overflow-auto flex-grow-1">
                            <transition-group name="list">
                                <div v-for="m in sortedMeetings" :key="m.id" 
                                    class="meeting-card mb-3 p-4 rounded-4"
                                    :class="m.isActive ? 'active-border' : 'upcoming-border'">
                                
                                    <div class="row align-items-center">
                                        <div class="col-md-2 border-end text-center">
                                            <div class="fw-bold fs-3 text-dark">{{ m.startTime }}</div>
                                            <small class="text-muted">ដល់ {{ m.endTime }}</small>
                                        </div>

                                        <div class="col-md-7 px-4">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="status-badge" :class="m.isActive ? 'bg-coral' : 'bg-sky'">{{ m.statusText }}</span>
                                                <span class="badge bg-light text-dark border khmer-font fw-normal">ជាន់ទី {{ m.floor }}</span>
                                                <span class="badge bg-light text-dark border khmer-font fw-normal">បន្ទប់ {{ m.room }}</span>
                                            </div>
                                            <h4 class="khmer-font fw-bold mb-1">{{ m.title }}</h4>
                                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ m.location }}</small>
                                        </div>

                                        <div class="col-md-3 text-md-end d-flex align-items-center justify-content-end">
                                            <div class="me-3">
                                                <div class="fw-bold khmer-font small">{{ m.host }}</div>
                                                <div class="fs-tiny text-muted text-uppercase">ប្រធានអង្គប្រជុំ</div>
                                            </div>
                                            <div class="avatar shadow-sm"><i class="bi bi-person-fill"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </transition-group>
                        </div>
                    </div>
                </main>

            </div>
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

<style scoped>
.monitor {
    --coral: #e76f51;
    --sky: #3498db;
    --success: #10b981;
    --border-color: #edf2f7;
    
    height: calc(100vh - 72px);
    background-color: var(--bg-light);
    overflow: hidden;
}

/* 2. Typography */
.khmer-font { 
    font-family: 'Kantumruy Pro', sans-serif !important; 
}

.fs-tiny { 
    font-size: 0.65rem; 
    font-weight: 700; 
    letter-spacing: 0.5px;
}

/* 3. Stat Components */
.stat-pill { 
    padding: 1.25rem; 
    border-radius: 1rem; 
    border-left: 5px solid; 
    transition: transform 0.2s ease;
}

.stat-pill.coral { background: #fff5f2; border-color: var(--coral); color: var(--coral); }
.stat-pill.sky { background: #f0f9ff; border-color: var(--sky); color: var(--sky); }

/* 4. Meeting Cards */
.meeting-card { 
    background: white; 
    border: 1px solid var(--border-color); 
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.active-border { 
    border-left: 6px solid var(--coral); 
    background: linear-gradient(90deg, #fffcfb 0%, #ffffff 100%); 
}

.upcoming-border { 
    border-left: 6px solid var(--sky); 
}

.meeting-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* 5. UI Elements */
.status-badge { 
    font-size: 0.75rem; 
    padding: 4px 12px; 
    border-radius: 50px; 
    color: white; 
    font-weight: 600; 
}

.bg-coral { background-color: var(--coral); }
.bg-sky { background-color: var(--sky); }

.avatar { 
    width: 45px; 
    height: 45px; 
    border-radius: 12px; 
    background: #f1f5f9; 
    display: grid; 
    place-items: center; 
    color: #64748b;
}

/* 6. Animations & Utilities */
.dot-pulse { 
    width: 10px; 
    height: 10px; 
    border-radius: 50%; 
    background: var(--success); 
    animation: pulse 2s infinite; 
}

@keyframes pulse { 
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.custom-scrollbar {
    overflow-y: auto;
}

.custom-scrollbar::-webkit-scrollbar { 
    width: 4px; 
}

.custom-scrollbar::-webkit-scrollbar-thumb { 
    background: #cbd5e1; 
    border-radius: 10px; 
}
</style>