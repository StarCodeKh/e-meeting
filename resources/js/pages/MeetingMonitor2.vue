<template>
    <div class="signage-wrapper">
        <div class="ambient-glow"></div>

        <header class="main-header">
            <div class="brand-container">
                <div class="logo-circle shadow-sm">
                <img :src="logo" alt="MEF" class="logo-img" />
                </div>
                <div class="brand-text">
                <h1 class="khmer-font responsive-title">ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ</h1>
                <p class="khmer-font responsive-subtitle text-primary">អគ្គលេខាធិការដ្ឋានគណៈកម្មាធិការដឹកនាំការងារកែទម្រង់ការគ្រប់គ្រងហិរញ្ញវត្ថុសាធារណៈ</p>
                </div>
            </div>
            
            <div class="time-container text-end">
                <div class="khmer-font fluid-clock tabular-nums">{{ currentTime }}</div>
                <div class="khmer-font fluid-date text-muted">{{ currentDateKhmer }}</div>
            </div>
        </header>

        <main class="meeting-viewport">
            <div v-if="isLoading" class="loader khmer-font">កំពុងទាញយកទិន្នន័យ...</div>
            
            <div v-else class="responsive-stack">
                <div v-for="(m, index) in sortedMeetings" :key="index" 
                    :class="['meeting-card', m.status]">
                
                <div class="section-time">
                    <div class="time-main tabular-nums">{{ m.displayStartTime }}</div>
                    <div class="time-meta khmer-font">
                    <span class="text-muted">ដល់ {{ m.displayEndTime }}</span>
                    <span class="badge-period">{{ parseInt(m.startTime) < 12 ? 'ព្រឹក' : 'រសៀល' }}</span>
                    </div>
                </div>

                <div class="section-info">
                    <h2 class="khmer-font meeting-title">{{ m.title }}</h2>
                    <div class="meta-grid khmer-font">
                    <div class="meta-item">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        <span>{{ m.location || 'សាលប្រជុំ' }}</span>
                    </div>
                    <div class="meta-item border-start-lg ps-lg-3">
                        <i class="bi bi-person-badge text-primary"></i>
                        <span>ដឹកនាំដោយ៖ <b class="text-dark">{{ m.participantsDisplay }}</b></span>
                    </div>
                    </div>
                </div>

                <div class="section-status">
                    <div :class="['status-box', m.status]">
                    <div v-if="m.status === 'active'" class="wave-loader">
                        <span></span><span></span><span></span>
                    </div>
                    <span class="khmer-font fw-bold">{{ m.statusText }}</span>
                    </div>
                </div>
                </div>
            </div>
        </main>

        <footer class="main-footer khmer-font">
            <div class="footer-left">
                <span>កិច្ចប្រជុំសរុប: <b class="text-primary">{{ meetings.length }}</b></span>
                <span class="status-indicator">
                <span class="dot-live"></span> ប្រព័ន្ធប្រជុំអេឡិចត្រូនិក
                </span>
            </div>
            <div class="footer-right text-muted">ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ</div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import logo from '@/assets/images/logo.png'
import { MeetingMonitor } from '@/services/MeetingMonitor'

const meetings = ref([])
const currentTime = ref('')
const currentDateKhmer = ref('')
const isLoading = ref(true)
let timer = null

const toKhmerNumeral = (num) => {
    if (!num) return ''
    const khmerNumbers = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩']
    return num.toString().replace(/\d/g, (digit) => khmerNumbers[digit])
}

const calculateStatus = (start, end) => {
    const now = new Date()
    const nowMin = now.getHours() * 60 + now.getMinutes()
    const toMinutes = (timeStr) => {
        if (!timeStr || timeStr === '--:--') return null
        const [h, m] = timeStr.split(':').map(Number)
        return h * 60 + m
    }
    const sMin = toMinutes(start)
    let eMin = toMinutes(end) || (sMin + 60)

    // LOGIC: If current time is past end time (e.g. now 21:00 vs 14:42), it is Completed
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
    const now = new Date()
    const days = ['ថ្ងៃអាទិត្យ', 'ថ្ងៃច័ន្ទ', 'ថ្ងៃអង្គារ', 'ថ្ងៃពុធ', 'ថ្ងៃព្រហស្បតិ៍', 'ថ្ងៃសុក្រ', 'ថ្ងៃសៅរ៍']
    const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ']
    currentDateKhmer.value = `${days[now.getDay()]} ទី${toKhmerNumeral(now.getDate())} ខែ${months[now.getMonth()]} ឆ្នាំ${toKhmerNumeral(now.getFullYear())}`
    currentTime.value = toKhmerNumeral(now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }))
}

onMounted(() => { updateTime(); fetchMeetingsData(); timer = setInterval(() => { updateTime(); if (new Date().getSeconds() === 0) fetchMeetingsData() }, 1000) })
onUnmounted(() => { clearInterval(timer) })
</script>

<style scoped>
    /* Responsive Foundation */
.signage-wrapper {
    background-color: #f0f4f8;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 2vw;
    position: relative;
    overflow-x: hidden;
}

.ambient-glow {
    position: absolute;
    width: 50vw;
    height: 50vw;
    background: radial-gradient(circle, rgba(13, 110, 253, 0.05) 0%, transparent 70%);
    top: -10vw;
    right: -10vw;
    pointer-events: none;
}

/* Fluid Header */
.main-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    padding: 1.5rem 2.5rem;
    border-radius: 20px;
    border-bottom: 5px solid #0d6efd;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    gap: 1rem;
}

.brand-container {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.logo-circle {
    background: #fff;
    padding: 0.8rem;
    border-radius: 20%;
}

.logo-img {
    height: clamp(40px, 5vw, 70px);
}

.responsive-title {
    font-size: clamp(1.2rem, 2.5vw, 2.2rem);
    font-weight: 800;
}

.responsive-subtitle {
    font-size: clamp(0.8rem, 1.2vw, 1.1rem);
}

.fluid-clock {
    font-size: clamp(2rem, 5vw, 4rem);
    font-weight: 800;
    color: #0d6efd;
    line-height: 1;
}

.fluid-date {
    font-size: clamp(0.8rem, 1.5vw, 1.2rem);
}

/* Responsive Meeting Cards */
.responsive-stack {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.meeting-card {
    display: grid;
    grid-template-columns: 1fr;
    /* Mobile First */
    background: #fff;
    border-radius: 18px;
    padding: 1.5rem;
    border: 1px solid #eef2f6;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    transition: transform 0.3s ease;
}

/* Desktop: 3 Column Grid */
@media (min-width: 992px) {
    .meeting-card {
        grid-template-columns: 180px 1fr 200px;
        align-items: center;
        padding: 1.5rem 2.5rem;
    }

    .section-info {
        padding: 0 3rem;
        border-left: 1px solid #f1f5f9;
    }
}

.meeting-card.active {
    border-left: 10px solid #198754;
    background: #f0fff4;
    transform: scale(1.01);
    
}



.meeting-card.completed {
    border-left: 10px solid #adb5bd;
    opacity: 0.6;
    filter: grayscale(1);
}

.meeting-card.upcoming {
    border-left: 10px solid #0d6efd;
}

/* Content Sections */
.time-main {
    font-size: 2.2rem;
    font-weight: 800;
    color: #1e293b;
}

.time-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
}

.badge-period {
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 5px;
    font-size: 0.8rem;
}

.meeting-title {
    font-size: clamp(1.2rem, 2vw, 1.6rem);
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.meta-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    font-size: clamp(0.9rem, 1.2vw, 1.1rem);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #475569;
}

/* Status Styles */
.section-status {
    display: flex;
    justify-content: flex-end;
}

.status-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    border-radius: 12px;
    min-width: 160px;
    justify-content: center;
}

.status-box.active {
    background: #198754;
    color: #fff;
    box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
}

.status-box.upcoming {
    border: 2px solid #0d6efd;
    color: #0d6efd;
}

.status-box.completed {
    background: #e9ecef;
    color: #6c757d;
}

.wave-loader {
    display: flex;
    gap: 3px;
}

.wave-loader span {
    width: 3px;
    height: 15px;
    background: #fff;
    animation: wave 1s infinite;
}

@keyframes wave {

    0%,
    100% {
        transform: scaleY(0.4);
    }

    50% {
        transform: scaleY(1);
    }
}

/* Footer */
.main-footer {
    margin-top: auto;
    padding: 1.5rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #dee2e6;
    font-size: clamp(0.8rem, 1vw, 1rem);
}

.footer-left {
    display: flex;
    gap: 2rem;
}

.dot-live {
    width: 10px;
    height: 10px;
    background: #198754;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}
</style>