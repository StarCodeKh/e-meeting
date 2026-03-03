<template>
    <div class="signage-wrapper container-fluid p-0">
        <div class="bg-gradient-overlay"></div>
        <div class="glass-mesh"></div>

        <header class="main-header shadow-lg mx-3 mt-3 mb-4 rounded-3 bg-light">
            <div class="row w-100 align-items-center py-3 px-4 mx-0">
                <div class="col-lg-8 d-flex align-items-center">
                    <div class="logo-area me-4">
                        <img :src="logo" alt="MEF Logo" class="official-logo" />
                    </div>
                    <div class="title-area">
                        <h2 class="khmer-font h6 fw-bold mb-0 text-dark">ព្រះរាជាណាចក្រកម្ពុជា</h2>
                        <h2 class="khmer-font small text-muted mb-1">ជាតិ សាសនា ព្រះមហាក្សត្រ</h2>
                        <div class="header-divider my-1"></div>
                        <h1 class="khmer-font h3 fw-bold mb-0 text-success-dark">ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ</h1>
                        <p class="khmer-font small text-primary mb-0">អគ្គលេខាធិការដ្ឋាន គ.វ.ហ.</p>
                    </div>
                </div>
                <div class="col-lg-4 text-end border-start d-none d-lg-block">
                    <div class="khmer-font text-muted small mb-1">{{ currentDateKhmer }}</div>
                    <div class="khmer-font small digital-clock tabular-nums fw-black display-5 text-success-dark">
                        {{ currentTime }}
                    </div>
                </div>
            </div>
        </header>

        <main class="meeting-viewport container-fluid px-4">
    <div class="th-row khmer-font py-2 d-none d-lg-flex align-items-center text-white px-3 mb-2 opacity-75">
        <div class="col-lg-2 fw-bold">ម៉ោងប្រជុំ</div>
        <div class="col-lg-8 fw-bold border-start border-white border-opacity-25 ps-4">កម្មវត្ថុនៃកិច្ចប្រជុំ និង ព័ត៌មានលម្អិត</div>
        <div class="col-lg-2 fw-bold text-center">ស្ថានភាព</div>
    </div>

    <div v-if="isLoading" class="d-flex flex-column align-items-center justify-content-center py-5 khmer-font text-white">
        <div class="spinner-border text-light mb-3" role="status"></div>
        <span>កំពុងទាញយកទិន្នន័យ...</span>
    </div>
    
    <div v-else class="card-stack d-flex flex-column gap-3 pb-5">
        <div v-for="(m, index) in sortedMeetings" :key="index" 
            class="meeting-card row mx-0 align-items-center shadow-sm py-4 rounded-3 bg-light" 
            :style="{ borderLeft: `12px solid ${getPriorityColor(m.color_id)}` }">
        
            <div class="col-12 col-lg-2 khmer-font mb-3 mb-lg-0">
                <div class="time-start display-6 fw-bold tabular-nums" :style="{ color: getPriorityColor(m.color_id) }">
                    {{ m.displayStartTime }}
                </div>
                <div class="time-range small d-flex gap-2 align-items-center mt-1">
                    <span class="text-muted">ដល់ {{ m.displayEndTime }}</span>
                    <span class="badge bg-light text-dark border fw-normal">{{ m.period }}</span>
                </div>
            </div>

            <div class="col-12 col-lg-8 border-start-lg ps-lg-4 mb-3 mb-lg-0">
                <h2 class="khmer-font h4 fw-bold mb-1 text-dark">{{ m.title }}</h2>

                <div v-if="m.description" class="description-area mb-2">
                    <p class="khmer-font text-muted small mb-0 opacity-75" style="line-height: 1.5;">
                        {{ m.description }}
                    </p>
                </div>

                <div class="meta-info khmer-font d-flex flex-wrap gap-x-4 gap-y-2 mt-2">
                    
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-geo-alt-fill me-2" :style="{ color: getPriorityColor(m.color_id) }"></i>
                        <span class="fw-bold text-dark">{{ m.location || 'សាលប្រជុំ' }}</span>
                        <span v-if="m.floor" class="ms-2 badge bg-primary bg-opacity-10 text-primary border-0">ជាន់ទី {{ toKhmerNumeral(m.floor) }}</span>
                        <span v-if="m.room" class="ms-1 badge bg-danger bg-opacity-10 text-danger border-0">បន្ទប់ {{ toKhmerNumeral(m.room) }}</span>
                    </div>

                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-person-fill me-2 text-primary"></i>
                        <span>ដឹកនាំ៖ <b class="text-dark">{{ m.participantsDisplay || m.leader }}</b></span>
                    </div>

                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-pencil-square me-2 text-success"></i>
                        <span>បញ្ចូលដោយ៖ <b class="text-dark">{{ m.creator_name || 'រដ្ឋបាល' }}</b></span>
                    </div>

                    <div v-if="m.attachment" class="d-flex align-items-center">
                        <a :href="m.attachment" 
                        target="_blank" 
                        class="text-decoration-none cursor-pointer">
                        
                            <div class="badge bg-danger text-white px-2 py-1 rounded-1 d-flex align-items-center shadow-sm animate-pulse">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                                <span style="font-size: 0.7rem;">ចុចដើម្បីមើលឯកសារ</span>
                            </div>
                            
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-2 text-center">
                <div class="status-box d-flex align-items-center justify-content-center gap-2 py-3 rounded-pill fw-bold shadow-sm" 
                    :style="getStatusBoxStyle(m)">
                    <div v-if="m.status === 'active'" class="pulse-dot"></div>
                    <span class="khmer-font fw-bold">{{ m.statusText }}</span>
                </div>
            </div>
        </div>
    </div>
</main>

        <footer class="main-footer bg-light py-3 px-4 border-top mt-auto shadow-lg position-relative">
            <div class="container-fluid d-flex justify-content-between align-items-center khmer-font">
                <div class="footer-left d-flex gap-4 align-items-center">
                    <span class="text-muted small">កិច្ចប្រជុំសរុប៖ <b class="text-primary">{{ toKhmerNumeral(meetings.length) }}</b></span>
                    <div class="vr d-none d-md-block"></div>
                    <span class="small d-flex align-items-center">
                        <span class="online-dot me-2"></span> 
                        <span class="text-muted text-uppercase">ប្រព័ន្ធប្រជុំអេឡិចត្រូនិក</span>
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
    import { getScheduleFormOptions } from '@/services/ScheduleTypes'

    // --- State Management ---
    const meetings = ref([])
    const priorities = ref([])
    const currentTime = ref('')
    const currentDateKhmer = ref('')
    const isLoading = ref(true)
    let timer = null

    // --- Helpers: ពណ៌តាមអាទិភាព (Priority Color) ---
    const getPriorityColor = (colorId) => {
        if (!colorId) return '#6c757d';
        const p = priorities.value.find(x => x.slug === colorId || x.id === colorId);
        return p ? p.color_hex : '#6c757d'; 
    };

    const getStatusBoxStyle = (meeting) => {
        const color = getPriorityColor(meeting.color_id);
        if (meeting.status === 'active') {
            return { backgroundColor: color, color: '#ffffff' };
        }
        return { 
            backgroundColor: `${color}15`, 
            color: color, 
            border: `1px solid ${color}30` 
        };
    }

    // --- Helpers: បំប្លែងលេខទៅជាលេខខ្មែរ (Khmer Numerals) ---
    const toKhmerNumeral = (num) => {
        if (num === null || num === undefined) return ''
        return num.toString().replace(/\d/g, (d) => ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'][d])
    }

    // --- Logic: គណនាស្ថានភាពកិច្ចប្រជុំ (Status Calculation) ---
    const calculateStatus = (start, end) => {
        const now = new Date()
        const nowMin = now.getHours() * 60 + now.getMinutes()
        const toMin = (t) => { 
            if(!t || t === '--:--') return null; 
            const [h, m] = t.split(':').map(Number); 
            return h * 60 + m 
        }
        const sMin = toMin(start); 
        const eMin = toMin(end) || (sMin + 60); // បើគ្មានម៉ោងបញ្ចប់ ឧបកិច្ចថា ៦០នាទី

        if (nowMin > eMin) return { code: 'completed', text: 'បានបញ្ចប់' }
        if (nowMin >= sMin && nowMin <= eMin) return { code: 'active', text: 'កំពុងប្រជុំ' }
        return { code: 'upcoming', text: 'មិនទាន់ប្រជុំ' }
    }

    // --- Data Fetching: ទាញយកទិន្នន័យពី API ---
    const fetchAllData = async () => {
        try {
            // ១. ទាញយក Option នៃ Priority សម្រាប់ពណ៌
            const options = await getScheduleFormOptions();
            if (options?.priorities) priorities.value = options.priorities;

            // ២. ទាញយកទិន្នន័យកិច្ចប្រជុំថ្ងៃនេះ
            const today = new Date().toISOString().split('T')[0]
            const data = await MeetingMonitor.getMeetingsByDate(today)
            
            // ៣. Mapping ទិន្នន័យឱ្យត្រូវជាមួយ UI
            meetings.value = (data || []).map(m => {
                const statusInfo = calculateStatus(m.startTime, m.endTime)
                
                return {
                    ...m,
                    status: statusInfo.code,
                    statusText: statusInfo.text,

                    // បន្ថែមជាន់ និង បន្ទប់ (ធានាថាបំប្លែងជាលេខខ្មែរក្នុង UI)
                    floor: m.floor || m.location_floor || null,
                    room: m.room || m.location_room || null,

                    // ឈ្មោះអ្នកបញ្ចូល (ទាញពី Relation user)
                    creator_name: m.creator_name || m.user?.name || 'រដ្ឋបាល',

                    // ឯកសារភ្ជាប់ (PDF)
                    attachment: m.attachment || m.file_path || m.pdf_file || null,

                    // ទម្រង់បង្ហាញម៉ោងជាខ្មែរ
                    displayStartTime: toKhmerNumeral(m.startTime || '00:00'),
                    displayEndTime: toKhmerNumeral(m.endTime || '--:--'),
                    
                    // បែងចែកពេលព្រឹក/រសៀល
                    period: parseInt(m.startTime) < 12 ? 'ព្រឹក' : 'រសៀល'
                }
            })
        } catch (e) { 
            console.error("Error fetching data:", e) 
        } finally { 
            isLoading.value = false 
        }
    }

    // --- Computed: រៀបលំដាប់កិច្ចប្រជុំ (Active មកមុនគេ) ---
    const sortedMeetings = computed(() => {
        const rank = { active: 1, upcoming: 2, completed: 3 }
        return [...meetings.value].sort((a, b) => rank[a.status] - rank[b.status])
    })

    // --- Logic: ម៉ោងឌីជីថល (Digital Clock) ---
    const updateTime = () => {
        const now = new Date();
        const days = ['អាទិត្យ', 'ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
        const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
        
        currentDateKhmer.value = `ថ្ងៃ${days[now.getDay()]} ទី${toKhmerNumeral(now.getDate())} ${months[now.getMonth()]} ${toKhmerNumeral(now.getFullYear())}`;
        
        currentTime.value = toKhmerNumeral(now.toLocaleTimeString('en-GB', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            hour12: false 
        }));
    }

    // --- Lifecycle Hooks ---
    onMounted(() => { 
        updateTime(); 
        fetchAllData(); 
        
        timer = setInterval(() => { 
            updateTime(); 
            if (new Date().getSeconds() === 0) fetchAllData();
        }, 1000) 
    })
    
    onUnmounted(() => {
        if (timer) clearInterval(timer);
    })
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

    .text-success-dark {
        color: #017827;
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