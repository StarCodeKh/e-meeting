<template>
    <div class="monitor-screen">
        <header class="monitor-header">
            <div class="header-left">
                <div class="logo-wrapper">
                    <img :src="logo" alt="MEF Logo" class="monitor-logo" />
                </div>
                
                <div class="header-text">
                <h1 class="khmer-font title">ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ</h1>
                <p class="khmer-font subtitle">អគ្គលេខាធិការដ្ឋានគណៈកម្មាធិការដឹកនាំការងារកែទម្រង់ការគ្រប់គ្រងហិរញ្ញវត្ថុសាធារណៈ</p>
                </div>
            </div>

            <div class="header-right text-end">
                <div class="date khmer-font text-white-50">{{ currentDateKhmer }}</div>
                <div class="time khmer-font tabular-nums text-cyan">{{ currentTime }}</div>
            </div>
        </header>

        <main class="monitor-content">
            <div class="table-container">
                <table class="monitor-table">
                    <thead>
                        <tr class="khmer-font">
                        <th class="center">ល.រ</th>
                        <th>ប្រធានបទ</th>
                        <th class="center">ចាប់ផ្តើម</th>
                        <th class="center">បញ្ចប់</th>
                        <th>កន្លែងប្រជុំ</th>
                        <th class="center">បន្ទប់</th>
                        <th class="center">ស្ថានភាព</th>
                        <th class="center">សកម្មភាព</th>
                        </tr>
                    </thead>

                    <tbody v-if="!isLoading">
                        <tr v-for="(m, index) in sortedMeetings" :key="m.id" :class="`row-${m.status}`">
                            <td class="center fw-bold text-cyan">{{ index + 1 }}</td>

                            <td class="khmer-font py-3">
                                <div class="title-text fw-bold fs- mb-1 text-white">
                                    {{ m.title }}
                                </div>

                                <div class="host-text d-flex align-items-center gap-2">
                                    <i class="bi bi-person-badge text-warning fs-6"></i>
                                    <span class="text-warning-50">ដឹកនាំដោយ៖</span>
                                    <span :class="['fw-bold fs-6', m.status === 'active' ? 'text-success' : 'text-info']">
                                        {{ m.host }}
                                    </span>
                                </div>
                            </td>

                            <td class="center tabular-nums fw-bold">
                                <div class="time-wrapper">
                                    <i class="bi bi-clock icon-sm"></i>
                                    <span>{{ m.startTime }}</span>
                                </div>
                            </td>

                            <td class="center tabular-nums fw-bold">
                                <div class="time-wrapper">
                                    <i class="bi bi-clock icon-sm"></i>
                                    <span>{{ m.endTime }}</span>
                                </div>
                            </td>

                            <td class="khmer-font">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                {{ m.location }}
                            </td>

                            <td class="center">
                                <span class="room-badge" v-if="m.room">{{ m.room }}</span>
                                <span v-else>-</span>
                            </td>

                            <td class="center khmer-font">
                                <span class="status-badge" :class="m.status">
                                <span v-if="m.status === 'active'" class="live-dot"></span>
                                {{ m.statusText }}
                                </span>
                            </td>

                            <td class="center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a v-if="m.link && m.status !== 'completed'" :href="m.link" target="_blank" rel="noopener noreferrer" class="btn-action" title="ចូលរួមប្រជុំ" @click.stop>
                                        <i class="bi bi-camera-video-fill"></i>
                                    </a>

                                    <a v-if="m.attachmentUrl" :href="m.attachmentUrl" target="_blank" rel="noopener noreferrer" class="btn-action text-danger" title="មើលឯកសារ" @click.stop>
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>

                                    <span v-if="(!m.link && !m.attachmentUrl) || (m.status === 'completed' && !m.attachmentUrl)" class="text-white-50 small">
                                        <a class="btn-action">
                                            <i class="bi bi-camera-video-off"></i>
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <tbody v-else>
                        <tr>
                            <td colspan="8" class="center loading khmer-font">កំពុងទាញទិន្នន័យ...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="monitor-footer khmer-font">
            <div class="stats">
                <span>កិច្ចប្រជុំសរុប: <b class="text-cyan">{{ meetings.length }}</b></span>
                <span class="ms-4">កំពុងប្រជុំ: <b class="danger">{{ activeMeetingsCount }}</b></span>
            </div>
            <div class="system-status">
                <span class="live-pulse"></span>
                <span class="ms-2 khmer-font">ប្រព័ន្ធប្រជុំអេឡិចត្រូនិក</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, onUnmounted } from 'vue'
    import logo from '@/assets/images/logo.png'
    import { MeetingMonitor } from '@/services/MeetingMonitor'

    // --- ១. កំណត់ States ---
    const meetings = ref([])
    const currentTime = ref('')
    const currentDateKhmer = ref('')
    const isLoading = ref(true)
    let timer = null

    // --- ២. មុខងារជំនួយ (Helper Functions) ---

    /**
     * បំប្លែងលេខអារ៉ាប់ ទៅជាលេខខ្មែរ
     */
    const toKhmerNumeral = (num) => {
        if (num === null || num === undefined) return ''
        const khmerNumbers = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩']
        return num.toString().replace(/\d/g, (digit) => khmerNumbers[digit])
    }

    /**
     * គណនាស្ថានភាពកិច្ចប្រជុំ (Active, Upcoming, Completed)
     */
    const calculateStatus = (start, end) => {
        const now = new Date()
        const nowMin = now.getHours() * 60 + now.getMinutes()
        
        const toMinutes = (timeStr) => {
            if (!timeStr || timeStr === '--:--') return null
            const [h, m] = timeStr.split(':').map(Number)
            return h * 60 + m
        }

        const sMin = toMinutes(start)
        let eMin = toMinutes(end)

        // ប្រសិនបើគ្មានម៉ោងបញ្ចប់ សន្មតថាមានរយៈពេល ៦០ នាទី
        if (eMin === null) eMin = sMin + 60

        if (nowMin >= sMin && nowMin <= eMin) return { code: 'active', text: 'កំពុងប្រជុំ' }
        if (nowMin < sMin) return { code: 'upcoming', text: 'មិនទាន់ប្រជុំ' }
        return { code: 'completed', text: 'បានបញ្ចប់' }
    }

    // --- ៣. ការទាញទិន្នន័យ (Data Fetching) ---
    const fetchMeetingsData = async () => {
        try {
            isLoading.value = true
            const today = new Date().toISOString().split('T')[0]
            
            const data = await MeetingMonitor.getMeetingsByDate(today)
            
            meetings.value = (data || []).map(m => {
                const sTime = m.startTime || '00:00'
                const eTime = m.endTime || '--:--'
                const statusInfo = calculateStatus(sTime, eTime)
                
                return {
                    ...m,
                    status: statusInfo.code,
                    statusText: statusInfo.text,
                    
                    link: m.link, 
                    attachmentUrl: m.attachmentUrl,

                    displayStartTime: toKhmerNumeral(sTime),
                    displayEndTime: eTime !== '--:--' ? toKhmerNumeral(eTime) : '--:--'
                }
            })
        } catch (err) {
            console.error("❌ Fetch Meetings Data Error:", err)
        } finally {
            isLoading.value = false
        }
    }

    // --- ៤. Computed Properties ---
    const activeMeetingsCount = computed(() => 
        meetings.value.filter(m => m.status === 'active').length
    )

    const sortedMeetings = computed(() => {
        const priority = { active: 1, upcoming: 2, completed: 3 }
        return [...meetings.value].sort((a, b) => priority[a.status] - priority[b.status])
    })

    // --- ៥. ពេលវេលា និង Lifecycle ---

    const updateTime = () => {
        const now = new Date()
        const days = ['ថ្ងៃអាទិត្យ', 'ថ្ងៃច័ន្ទ', 'ថ្ងៃអង្គារ', 'ថ្ងៃពុធ', 'ថ្ងៃព្រហស្បតិ៍', 'ថ្ងៃសុក្រ', 'ថ្ងៃសៅរ៍']
        const months = [
            'ខែមករា', 'ខែកុម្ភៈ', 'ខែមីនា', 'ខែមេសា', 'ខែឧសភា', 'ខែមិថុនា', 
            'ខែកក្កដា', 'ខែសីហា', 'ខែកញ្ញា', 'ខែតុលា', 'ខែវិច្ឆិកា', 'ខែធ្នូ'
        ]

        const dayName = days[now.getDay()]
        const monthName = months[now.getMonth()]
        const dayKhmer = toKhmerNumeral(now.getDate())
        const yearKhmer = toKhmerNumeral(now.getFullYear()) 

        currentDateKhmer.value = `${dayName} ទី${dayKhmer} ${monthName} ឆ្នាំ${yearKhmer}`

        const timeStr = now.toLocaleTimeString('en-GB', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            hour12: false 
        })
        currentTime.value = toKhmerNumeral(timeStr)
    }

    onMounted(() => {
        updateTime()
        fetchMeetingsData()
        
        // Update ម៉ោងរាល់វិនាទី និង Refresh Data រាល់នាទី
        timer = setInterval(() => {
            updateTime()
            if (new Date().getSeconds() === 0) {
                fetchMeetingsData()
            }
        }, 1000)
    })

    onUnmounted(() => {
        if (timer) clearInterval(timer)
    })
</script>

<style scoped>
    @import url('@/assets/css/style.css');
    @import url('@/css/monitor-list.css');
</style>