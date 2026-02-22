<template>
    <div class="side-panel-wrapper">
        <div class="side-panel-container h-100">
            <div class="featured-card shadow-sm mb-4 overflow-hidden rounded-3 border-0">
                <Transition name="slide-fade" mode="out-in">
                    <div v-if="selectedDayMeetings.length > 0" :key="currentSlideIndex">
                        <div class="meeting-content">
                            <div class="time-header" :style="{ backgroundColor: getPriorityColor(activeMeeting.color_id) }">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <h1 class="display-time text-white mb-0 fw-bold">{{ toKhmerNum(activeMeeting.time) }}</h1>
                                        <div v-if="selectedDayMeetings.length > 1" class="slide-indicators d-flex gap-1">
                                            <span v-for="(m, idx) in selectedDayMeetings" :key="idx" 
                                                :class="['indicator-dot', { 'active': idx === currentSlideIndex }]"></span>
                                        </div>
                                    </div>
                                    <span class="badge bg-white bg-opacity-25 rounded-3 px-3 py-1 small khmer-font">
                                        {{ activeMeeting.period }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-4 bg-white">
                                <h6 class="khmer-font fw-bold text-muted small mb-1">ដឹកនាំដោយ៖</h6>
                                <h5 class="khmer-font leader-name fw-bold mb-3 text-dark">
                                    {{ activeMeeting.participants?.join(', ') || 'មិនមានទិន្នន័យ' }}
                                </h5>
                                <p class="khmer-font description-text text-muted mb-4 small line-clamp-3">
                                    {{ activeMeeting.description || 'មិនមានការពិពណ៌នា' }}
                                </p>
                                
                                <div class="meta-info mb-4 d-flex flex-column gap-2">
                                    <div class="meta-item small text-muted khmer-font">
                                        <i class="bi bi-geo-alt me-2" :style="{ color: getPriorityColor(activeMeeting.color_id) }"></i> 
                                        {{ activeMeeting.location || 'ទីស្តីការក្រសួង' }}
                                    </div>
                                    <div v-if="activeMeeting.room" class="meta-item small text-muted khmer-font">
                                        <i class="bi bi-door-open me-2" :style="{ color: getPriorityColor(activeMeeting.color_id) }"></i> 
                                        បន្ទប់៖ {{ activeMeeting.room }}
                                    </div>
                                </div>

                                <a v-if="activeMeeting.link && activeMeeting.link !== '#'" 
                                    :href="activeMeeting.link" target="_blank" 
                                    class="btn btn-conference w-100 fw-bold py-2 khmer-font rounded-3 text-white"
                                    :style="{ backgroundColor: getPriorityColor(activeMeeting.color_id), border: 'none' }">
                                    <i class="bi bi-camera-video me-2"></i> ចូលរួមប្រជុំអនឡាញ
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state p-5 text-center bg-white rounded-3 border border-dashed">
                        <i class="bi bi-calendar2-x fs-1 text-light mb-3 d-block"></i>
                        <p class="khmer-font text-muted small">មិនមានកិច្ចប្រជុំសម្រាប់ថ្ងៃនេះទេ</p>
                    </div>
                </Transition>
            </div>

            <div class="calendar-card shadow-sm bg-white p-4 rounded-3 border-0">
                <div class="calendar-nav d-flex justify-content-between align-items-center mb-4">
                    <div class="khmer-font fw-bold text-dark view-title">
                        {{ viewTitleKhmer }}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="nav-square-btn border shadow-none" @click="navigate(-1)"><i class="bi bi-chevron-left"></i></button>
                        <button class="nav-square-btn border shadow-none" @click="navigate(1)"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>

                <div class="view-switcher d-flex bg-light p-1 rounded-3 border mb-3">
                    <button v-for="view in ['week', 'month']" :key="view" 
                        @click="currentView = view" 
                        class="btn btn-sm flex-fill rounded-3 khmer-font transition-all"
                        :class="currentView === view ? 'btn-primary text-white shadow-sm' : 'border-0 text-muted'">
                        {{ view === 'week' ? 'សប្តាហ៍' : 'ខែ' }}
                    </button>
                </div>

                <div class="calendar-grid">
                    <div v-for="day in daysOfWeekKhmer" :key="day" class="grid-header khmer-font text-muted fw-bold text-center mb-2">
                        {{ day }}
                    </div>
                    
                    <template v-if="currentView === 'month'">
                        <div v-for="n in paddingDays" :key="'p'+n" class="grid-cell empty"></div>
                    </template>
                    
                    <div v-for="day in displayDays" :key="day.dateString" class="grid-cell" :class="{ 'is-today': day.isToday, 'is-selected': isSelected(day.dateObj) }" @click="handleDateClick(day)">
                        <span class="day-num">{{ toKhmerNum(day.date) }}</span>
                        <div class="dot-container">
                            <span v-for="e in day.events.slice(0, 2)" :key="e.id" class="status-dot" :style="{ backgroundColor: getPriorityColor(e.color_id) }"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
    import { MeetingCalendar } from '@/services/MeetingCalendar'
    import { getScheduleFormOptions } from '@/services/ScheduleTypes'

    // --- State ---
    const currentView = ref('month')
    const referenceDate = ref(new Date())
    const meetings = ref([])
    const priorities = ref([])
    const currentSlideIndex = ref(0)
    let slideInterval = null

    const daysOfWeekKhmer = ['អា', 'ច', 'អ', 'ព', 'ព្រ', 'សុ', 'ស']
    const khmerMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ']

    // --- Helpers ---
    const toKhmerNum = (num) => num?.toString().replace(/\d/g, (d) => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d]) || ''

    const getPriorityColor = (colorId) => {
        const p = priorities.value.find(x => x.slug === colorId || x.id === colorId);
        return p ? p.color_hex : '#6c757d';
    };

    // --- API Logic ---
    const fetchInitialData = async () => {
        try {
            const options = await getScheduleFormOptions();
            if (options?.priorities) priorities.value = options.priorities;
            await fetchMeetings();
        } catch (e) { console.error(e) }
    }

    const fetchMeetings = async () => {
        const month = referenceDate.value.getMonth() + 1
        const year = referenceDate.value.getFullYear()
        meetings.value = await MeetingCalendar.getMeetingsByMonth(month, year)
    }

    onMounted(() => {
        fetchInitialData()
        startAutoSlide()
    })

    onUnmounted(() => stopAutoSlide())

    // --- Slider ---
    const startAutoSlide = () => {
        stopAutoSlide();
        slideInterval = setInterval(() => {
            if (selectedDayMeetings.value.length > 1) {
                currentSlideIndex.value = (currentSlideIndex.value + 1) % selectedDayMeetings.value.length
            }
        }, 5000)
    }
    const stopAutoSlide = () => { if (slideInterval) clearInterval(slideInterval) }

    // --- Computed ---
    const viewTitleKhmer = computed(() => `ខែ${khmerMonths[referenceDate.value.getMonth()]} ឆ្នាំ${toKhmerNum(referenceDate.value.getFullYear())}`)
    const selectedDayMeetings = computed(() => meetings.value.filter(m => m.date === referenceDate.value.toLocaleDateString('en-CA')))
    const activeMeeting = computed(() => selectedDayMeetings.value[currentSlideIndex.value] || null)

    const displayDays = computed(() => {
        if (currentView.value === 'month') return monthDays.value
        const start = new Date(referenceDate.value); start.setDate(referenceDate.value.getDate() - referenceDate.value.getDay())
        return Array.from({ length: 7 }, (_, i) => {
            const d = new Date(start); d.setDate(start.getDate() + i); return generateDay(d)
        })
    })

    const monthDays = computed(() => {
        const y = referenceDate.value.getFullYear(), m = referenceDate.value.getMonth();
        const last = new Date(y, m + 1, 0).getDate()
        return Array.from({ length: last }, (_, i) => generateDay(new Date(y, m, i + 1)))
    })

    const generateDay = (d) => {
        const s = d.toLocaleDateString('en-CA')
        return { date: d.getDate(), dateObj: new Date(d), dateString: s, isToday: new Date().toDateString() === d.toDateString(), events: meetings.value.filter(e => e.date === s) }
    }

    const paddingDays = computed(() => new Date(referenceDate.value.getFullYear(), referenceDate.value.getMonth(), 1).getDay())

    // --- Methods ---
    const navigate = (s) => {
        const d = new Date(referenceDate.value)
        currentView.value === 'month' ? d.setMonth(d.getMonth() + s) : d.setDate(d.getDate() + (s * 7))
        referenceDate.value = d
    }
    const handleDateClick = (day) => { referenceDate.value = day.dateObj }
    const isSelected = (d) => referenceDate.value.toDateString() === d.toDateString()

    watch(referenceDate, (n, o) => {
        currentSlideIndex.value = 0
        if (n.getMonth() !== o.getMonth() || n.getFullYear() !== o.getFullYear()) fetchMeetings()
    })
</script>

<style scoped>
	@import url('../css/left-panel.css');
</style>