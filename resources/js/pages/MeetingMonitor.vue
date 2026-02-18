<template>
    <div class="signage-wrapper container-fluid p-0">
        <div class="bg-gradient-overlay"></div>
        <div class="glass-mesh"></div>

        <header class="main-header shadow-lg mx-3 mt-3 mb-4 rounded-3 bg-white">
            <div class="row w-100 align-items-center py-3 px-4 mx-0">
                <div class="col-lg-8 d-flex align-items-center">
                    <div class="logo-area me-4">
                        <img :src="logo" alt="MEF Logo" class="official-logo" />
                    </div>
                    <div class="title-area">
                        <h2 class="khmer-font h6 fw-bold mb-0 text-dark">ព្រះរាជាណាចក្រកម្ពុជា</h2>
                        <h2 class="khmer-font small text-muted mb-1">ជាតិ សាសនា ព្រះមហាក្សត្រ</h2>
                        <div class="header-divider my-1"></div>
                        <h1 class="khmer-font h3 fw-bold mb-0 text-primary-dark">ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ</h1>
                        <p class="khmer-font small text-primary mb-0">អគ្គលេខាធិការដ្ឋាន គ.វ.ហ.</p>
                    </div>
                </div>
                <div class="col-lg-4 text-end border-start d-none d-lg-block">
                    <div class="khmer-font text-muted small mb-1">{{ currentDateKhmer }}</div>
                    <div class="khmer-font small digital-clock tabular-nums fw-black display-5 text-primary-dark">
                        {{ currentTime }}
                    </div>
                </div>
            </div>
        </header>

        <main class="meeting-viewport container-fluid px-4">
        
            <div class="th-row khmer-font py-2 d-none d-lg-flex align-items-center text-white-50 px-3">
                <div class="col-lg-2 fw-bold">ម៉ោងប្រជុំ</div>
                <div class="col-lg-8 fw-bold border-start border-secondary ps-4">កម្មវត្ថុនៃកិច្ចប្រជុំ និង អ្នកចូលរួម</div>
                <div class="col-lg-2 fw-bold text-center">ស្ថានភាព</div>
            </div>

            <div v-if="isLoading" class="d-flex flex-column align-items-center justify-content-center py-5 khmer-font text-white">
                <div class="spinner-border text-light mb-3" role="status"></div>
                <span>កំពុងទាញយកទិន្នន័យ...</span>
            </div>
            
            <div v-else class="card-stack d-flex flex-column gap-3 pb-5">
                <div v-for="(m, index) in sortedMeetings" :key="index" class="meeting-card row mx-0 align-items-center shadow-sm py-3 rounded-3 bg-white" :style="{ borderLeft: `12px solid ${getStatusColor(m.status)}` }">
                
                <div class="col-12 col-lg-2 khmer-font mb-3 mb-lg-0">
                    <div class="time-start display-6 fw-bold tabular-nums" :style="{ color: getStatusColor(m.status) }">
                        {{ m.displayStartTime }}
                    </div>
                    <div class="time-range small d-flex gap-2 align-items-center">
                        <span class="text-muted">ដល់ {{ m.displayEndTime }}</span>
                        <span class="badge bg-light text-dark border fw-normal">{{ parseInt(m.startTime) < 12 ? 'ព្រឹក' : 'រសៀល' }}</span>
                    </div>
                </div>

                <div class="col-12 col-lg-8 border-start-lg ps-lg-4 mb-3 mb-lg-0">
                    <h2 class="khmer-font h4 fw-bold mb-2 text-dark">{{ m.title }}</h2>
                    <div class="meta-info khmer-font d-flex flex-wrap gap-4">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="bi bi-geo-alt-fill me-2" :style="{ color: getStatusColor(m.status) }"></i>
                            <span>{{ m.location || 'សាលប្រជុំ' }}</span>
                        </div>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="bi bi-person-fill me-2 text-primary"></i>
                            <span>ដឹកនាំដោយ៖ <b class="text-dark">{{ m.participantsDisplay }}</b></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-2">
                    <div class="status-box d-flex align-items-center justify-content-center gap-2 py-3 rounded-3 fw-bold shadow-sm" :style="getStatusBoxStyle(m.status)">
                        <div v-if="m.status === 'active'" class="pulse-dot"></div>
                            <span class="khmer-font fw-bold">{{ m.statusText }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="main-footer bg-white py-3 px-4 border-top mt-auto shadow-lg position-relative">
            <div class="container-fluid d-flex justify-content-between align-items-center khmer-font">
                <div class="footer-left d-flex gap-4 align-items-center">
                    <span class="text-muted small">កិច្ចប្រជុំសរុប៖ <b class="text-primary">{{ meetings.length }}</b></span>
                    <div class="vr d-none d-md-block"></div>
                    <span class="small d-flex align-items-center">
                        <span class="online-dot me-2"></span> 
                        <span class="text-muted">ប្រព័ន្ធគ្រប់គ្រងកាលវិភាគឌីជីថល</span>
                    </span>
                </div>
                <div class="footer-right text-muted small fw-bold">
                    ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, onUnmounted } from 'vue'
    import logo from '@/assets/images/logo.png'
    import { MeetingMonitor } from '@/services/MeetingMonitor'

    const statusConfigs = {
        active: {
            color: '#dc3545', // Standard Red
            bg: '#fff5f5',
            boxBg: '#dc3545',
            boxText: '#ffffff'
        },
        upcoming: {
            color: '#0d6efd', // MEF Blue
            bg: '#ffffff',
            boxBg: 'transparent',
            boxText: '#0d6efd',
            border: '2px solid #0d6efd'
        },
        completed: {
            color: '#6c757d', // Muted Gray
            bg: '#f8f9fa',
            boxBg: '#e9ecef',
            boxText: '#6c757d'
        }
    }

    const meetings = ref([])
    const currentTime = ref('')
    const currentDateKhmer = ref('')
    const isLoading = ref(true)
    let timer = null

    // Helper for dynamic colors
    const getStatusColor = (status) => statusConfigs[status]?.color || '#000'

    const getStatusBoxStyle = (status) => {
        const config = statusConfigs[status]
        return {
            backgroundColor: config.boxBg,
            color: config.boxText,
            border: config.border || 'none'
        }
    }

    // --- DATA LOGIC ---
    const toKhmerNumeral = (num) => {
        if (!num) return ''
        const khmerNumbers = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩']
        return num.toString().replace(/\d/g, (digit) => khmerNumbers[digit])
    }

    const calculateStatus = (start, end) => {
        const now = new Date()
        const nowMin = now.getHours() * 60 + now.getMinutes()
        const toMinutes = (t) => { if (!t || t === '--:--') return null; const [h, m] = t.split(':').map(Number); return h * 60 + m }
        const sMin = toMinutes(start); let eMin = toMinutes(end) || (sMin + 60)
        if (nowMin > eMin) return { code: 'completed', text: 'បានបញ្ចប់' }
        if (nowMin >= sMin && nowMin <= eMin) return { code: 'active', text: 'កំពុងប្រជុំ' }
        return { code: 'upcoming', text: 'មិនទាន់ប្រជុំ' }
    }

    const fetchMeetingsData = async () => {
        try {
            const today = new Date().toISOString().split('T')[0]
            const data = await MeetingMonitor.getMeetingsByDate(today)
            meetings.value = (data || []).map(m => {
                const statusInfo = calculateStatus(m.startTime, m.endTime)
                return {
                    ...m,
                    status: statusInfo.code,
                    statusText: statusInfo.text,
                    displayStartTime: toKhmerNumeral(m.startTime),
                    displayEndTime: toKhmerNumeral(m.endTime || '--:--')
                }
            })
        } catch (e) { console.error(e) } finally { isLoading.value = false }
    }

    const sortedMeetings = computed(() => {
        const priority = { active: 1, upcoming: 2, completed: 3 }
        return [...meetings.value].sort((a, b) => priority[a.status] - priority[b.status])
    })

    const updateTime = () => {
        const now = new Date(); const days = ['អាទិត្យ', 'ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍']; const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ']
        currentDateKhmer.value = `ថ្ងៃ${days[now.getDay()]} ទី${toKhmerNumeral(now.getDate())} ${months[now.getMonth()]} ${toKhmerNumeral(now.getFullYear())}`
        currentTime.value = toKhmerNumeral(now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }))
    }

    onMounted(() => { updateTime(); fetchMeetingsData(); timer = setInterval(() => { updateTime(); if (new Date().getSeconds() === 0) fetchMeetingsData() }, 1000) })
    onUnmounted(() => { clearInterval(timer) })
</script>

<style scoped>
    .signage-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: #0f172a;
        position: relative;
        overflow-x: hidden;
    }

    .bg-gradient-overlay {
        position: absolute;
        inset: 0;
        transition: background 1s ease;
        pointer-events: none;
    }

    .glass-mesh {
        position: absolute;
        inset: 0;
        opacity: 0.05;
        background-image: radial-gradient(#ffffff 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .text-primary-dark {
        color: #1e3a8a;
    }

    .header-divider {
        height: 2px;
        width: 80px;
        background: #e2e8f0;
    }

    .official-logo {
        height: 85px;
        width: auto;
    }

    .digital-clock {
        line-height: 1;
        letter-spacing: -1px;
    }

    .meeting-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-top: none;
        border-right: none;
        border-bottom: none;
    }

    .meeting-card:hover {
        transform: translateY(-5px);
    }

    @media (min-width: 992px) {
        .border-start-lg {
            border-left: 1px solid #f1f5f9 !important;
        }
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: pulse-active 1.5s infinite;
    }

    @keyframes pulse-active {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
    }

    .online-dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
    }

    .th-row {
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }
</style>