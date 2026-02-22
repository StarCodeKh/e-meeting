<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        
        <div class="row g-3 h-100">
            <div class="col-lg-3 d-flex flex-column h-100">
                <LeftPanel/>
            </div>

            <div class="col-lg-9 d-flex flex-column h-100">
                <Timeline :redLineTop="62">
                    <template #morning>
                        <MeetingCard 
                            v-for="meeting in morningList" :key="meeting.id" :style="{ 
                                borderLeft: `4px solid ${getPriorityColor(meeting.color_id)}`,
                                backgroundColor: `${getPriorityColor(meeting.color_id)}15` 
                            }"
                            :time="toKhmerNum(meeting.time)" 
                            :title="meeting.title" 
                            :desc="meeting.description" 
                        />
                    </template>

                    <template #afternoon>
                        <MeetingCard 
                            v-for="meeting in afternoonList" 
                            :key="meeting.id" 
                            :style="{ 
                                borderLeft: `4px solid ${getPriorityColor(meeting.color_id)}`,
                                backgroundColor: `${getPriorityColor(meeting.color_id)}15` 
                            }"
                            :time="toKhmerNum(meeting.time)" 
                            :title="meeting.title" 
                            :desc="meeting.description" 
                        />
                    </template>
                </Timeline>
                <div class="calendar-card card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h4 class="khmer-font fw-bold mb-0 text-dark">{{ viewTitleKhmer }}</h4>
                                <p class="text-muted small mb-0 khmer-font">{{ currentRangeLabel }}</p>
                            </div>

                            <div class="d-flex gap-1 bg-light p-1 rounded-3 border shadow-none">
                                <button v-for="v in viewOptions" :key="v.id" class="btn btn-sm rounded-2 px-3 transition-all khmer-font" 
                                    :class="currentView === v.id ? 'btn-primary shadow-sm text-white' : 'btn-light text-muted border-0'" 
                                    @click="currentView = v.id">
                                    {{ v.label }}
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="nav-square-btn border shadow-none" @click="navigate(-1)"><i class="bi bi-chevron-left"></i></button>
                                <button class="nav-square-btn border shadow-none" @click="goToToday"><i class="bi bi-calendar-event"></i></button>
                                <button class="nav-square-btn border shadow-none" @click="navigate(1)"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0 mt-3">
                        <div v-if="isLoading" class="p-5 text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <div v-else-if="currentView === 'month'" class="fade-in">
                            <div class="calendar-grid bg-light py-2 border-top border-bottom">
                                <div v-for="day in daysOfWeekKhmer" :key="day" class="text-center small fw-bold text-muted khmer-font">
                                    {{ day }}
                                </div>
                            </div>
                            <div class="calendar-grid p-2">
                                <div v-for="n in paddingDays" :key="'p-'+n" class="day-cell empty"></div>
                                <div v-for="day in monthDays" :key="day.dateString" class="day-cell" @click="handleDateSelection(day.dateObj)">
                                    <div class="khmer-font day-number fw-bold" :class="{ 'today-active': day.isToday, 'selected-active': isSelected(day.dateObj) }">
                                        {{ toKhmerNum(day.date) }}
                                    </div>
                                    <div class="event-indicator-container">
                                        <span v-for="e in day.events.slice(0, 3)" :key="e.id" 
                                              class="dot" 
                                              :style="{ backgroundColor: getPriorityColor(e.color_id) }">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="timeline-container p-4 fade-in overflow-auto" style="max-height: 600px;">
                            <div v-for="slot in timelineData" :key="slot.dateString" class="mb-5">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="khmer-font date-icon shadow-sm">
                                        <div class="month text-uppercase">{{ slot.monthShort }}</div>
                                        <div class="day">{{ slot.dayNumber }}</div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold khmer-font">{{ slot.dayName }}</h6>
                                        <span class="badge bg-light text-primary border rounded-3 small khmer-font">
                                            {{ toKhmerNum(slot.events.length) }} កិច្ចប្រជុំ
                                        </span>
                                    </div>
                                </div>

                                <div v-if="slot.events.length === 0" class="empty-box khmer-font text-center p-4 border rounded-3 bg-light text-muted small">
                                    មិនមានកិច្ចប្រជុំគ្រោងទុកសម្រាប់ថ្ងៃនេះទេ
                                </div>

                                <div v-for="event in slot.events" :key="event.id" class="event-card mb-3 p-3 border-start border-4 rounded-3 shadow-sm bg-light" :style="`border-left-color: ${getPriorityColor(event.color_id)} !important;`">
                                    <div class="d-flex justify-content-between align-items-center">
                                        
                                        <div class="flex-grow-1">
                                            <div class="small text-muted khmer-font fw-bold mb-2">
                                                <i class="bi bi-clock me-1" :style="{ color: getPriorityColor(event.color_id) }"></i> 
                                                {{ toKhmerNum(event.time) }} 
                                                <span class="badge rounded-2 fw-normal px-2 py-1 small khmer-font ms-1" :style="getSubtleStyle(event.color_id)">
                                                    {{ event.period }}
                                                </span>

                                                <span class="mx-2 text-lowercase">ដល់</span>
                                                {{ toKhmerNum(event.endTime) }} 
                                                <span class="badge rounded-2 fw-normal px-2 py-1 small khmer-font ms-1" :style="getSubtleStyle(event.color_id)">
                                                    {{ getPeriod(event.endTimeRaw) }}
                                                </span>
                                            </div>

                                            <h6 class="khmer-font fw-bold mt-2 mb-2 text-dark">{{ event.title }}</h6>
                                            
                                            <div class="d-flex flex-wrap gap-3">
                                                <div v-if="event.room" class="small text-muted khmer-font">
                                                    <i class="bi bi-door-open me-1" :style="{ color: getPriorityColor(event.color_id) }"></i> 
                                                    <strong>បន្ទប់៖</strong> {{ event.room }}
                                                </div>
                                                <div v-if="event.participants?.length" class="small text-muted khmer-font">
                                                    <i class="bi bi-person-check me-1" :style="{ color: getPriorityColor(event.color_id) }"></i> 
                                                    <strong>ដឹកនាំដោយ៖</strong> {{ event.participants.join(', ') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 ms-3 align-self-center">
                                            <a v-if="event.link && event.link !== '#'" :href="event.link" target="_blank" class="btn-action shadow-none border d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; border-radius: 8px;" title="ចូលរួមប្រជុំ">
                                                <i class="bi bi-camera-video-fill text-primary"></i>
                                            </a>
                                            
                                            <a v-if="event.attachmentUrl || event.attachment" :href="event.attachmentUrl || event.attachment" target="_blank" class="btn-action shadow-none border d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; border-radius: 8px;" title="មើលឯកសារ">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, computed, onMounted, watch } from 'vue'
    import DashboardLayout from '../components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'
    import LeftPanel from '@/components/LeftPanel.vue'
    import { MeetingCalendar } from '@/services/MeetingCalendar'
    import { getScheduleFormOptions } from '@/services/ScheduleTypes'

    // --- ១. State Management ---
    const currentView = ref('month')
    const referenceDate = ref(new Date())
    const meetings = ref([])
    const priorities = ref([]) 
    const isLoading = ref(false)

    const daysOfWeekKhmer = ['អាទិត្យ', 'ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍']
    const viewOptions = [
        { id: 'today', label: 'ថ្ងៃនេះ' },
        { id: 'week', label: 'សប្តាហ៍' },
        { id: 'month', label: 'ខែ' }
    ]
    const khmerMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ']

    // --- ២. Khmer Helpers ---
    const toKhmerNum = (num) => {
        if (num === null || num === undefined) return ''
        return num.toString().replace(/\d/g, (d) => ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'][d])
    }

    const getPeriod = (timeStr) => {
        if (!timeStr) return '';
        const hour = parseInt(timeStr.split(':')[0]);
        return hour >= 12 ? 'រសៀល' : 'ព្រឹក';
    }

    // --- ៣. Dynamic Color Functions ---
    const getPriorityColor = (colorId) => {
        const priority = priorities.value.find(p => p.slug === colorId || p.id === colorId);
        return priority ? priority.color_hex : '#6c757d'; 
    };

    const getSubtleStyle = (colorId) => {
        const hex = getPriorityColor(colorId);
        return {
            backgroundColor: `${hex}15`, 
            color: hex,
            border: `1px solid ${hex}30`
        };
    };

    // --- ៤. API Fetching Logic ---
    const fetchMeetingsData = async () => {
        try {
            isLoading.value = true
            
            // ទាញយក Priorities ជាមុន
            const options = await getScheduleFormOptions();
            if (options?.priorities) priorities.value = options.priorities;

            let data = []
            if (currentView.value === 'month') {
                data = await MeetingCalendar.getMeetingsByMonth(referenceDate.value.getMonth() + 1, referenceDate.value.getFullYear())
            } else {
                data = await MeetingCalendar.getMeetingsByDate(referenceDate.value.toLocaleDateString('en-CA'))
            }
            
            meetings.value = data.map(m => ({
                ...m,
                date: m.date || referenceDate.value.toLocaleDateString('en-CA'),
                color_id: m.color_id 
            }))
        } catch (error) {
            console.error("Fetch Error:", error)
        } finally {
            isLoading.value = false
        }
    }

    onMounted(fetchMeetingsData)
    watch([referenceDate, currentView], fetchMeetingsData)

    // --- ៥. Computed Properties (UI) ---
    const viewTitleKhmer = computed(() => `ខែ${khmerMonths[referenceDate.value.getMonth()]} ឆ្នាំ${toKhmerNum(referenceDate.value.getFullYear())}`)

    const currentRangeLabel = computed(() => {
        if (currentView.value === 'today') {
            return `ថ្ងៃ${daysOfWeekKhmer[referenceDate.value.getDay()]} ទី${toKhmerNum(referenceDate.value.getDate())} ខែ${khmerMonths[referenceDate.value.getMonth()]}`
        }
        return 'តារាងពេលវេលាកិច្ចប្រជុំ'
    })

    const morningList = computed(() => {
        const dStr = referenceDate.value.toLocaleDateString('en-CA')
        return meetings.value.filter(m => m.date === dStr && m.session === 'morning')
    })

    const afternoonList = computed(() => {
        const dStr = referenceDate.value.toLocaleDateString('en-CA')
        return meetings.value.filter(m => m.date === dStr && m.session === 'afternoon')
    })

    const paddingDays = computed(() => new Date(referenceDate.value.getFullYear(), referenceDate.value.getMonth(), 1).getDay())

    const monthDays = computed(() => {
        const year = referenceDate.value.getFullYear(), month = referenceDate.value.getMonth();
        const lastDay = new Date(year, month + 1, 0).getDate();
        return Array.from({ length: lastDay }, (_, i) => {
            const d = new Date(year, month, i + 1), dStr = d.toLocaleDateString('en-CA');
            return { date: i+1, dateObj: d, dateString: dStr, isToday: new Date().toDateString() === d.toDateString(), events: meetings.value.filter(e => e.date === dStr) }
        })
    })

    const timelineData = computed(() => {
        const dates = []; const tempDate = new Date(referenceDate.value); const count = currentView.value === 'today' ? 1 : 7;
        if (currentView.value === 'week') tempDate.setDate(tempDate.getDate() - tempDate.getDay());
        for (let i = 0; i < count; i++) {
            const d = new Date(tempDate); d.setDate(d.getDate() + i); const dStr = d.toLocaleDateString('en-CA');
            dates.push({ dateString: dStr, dayNumber: toKhmerNum(d.getDate()), dayName: daysOfWeekKhmer[d.getDay()], monthShort: khmerMonths[d.getMonth()], events: meetings.value.filter(e => e.date === dStr) })
        }
        return dates
    })

    // --- ៦. Methods ---
    const navigate = (step) => {
        const d = new Date(referenceDate.value);
        if (currentView.value === 'month') d.setMonth(d.getMonth() + step);
        else if (currentView.value === 'week') d.setDate(d.getDate() + (step * 7));
        else d.setDate(d.getDate() + step);
        referenceDate.value = d;
    }
    const handleDateSelection = (date) => { referenceDate.value = date; currentView.value = 'today'; }
    const goToToday = () => referenceDate.value = new Date();
    const isSelected = (date) => referenceDate.value.toDateString() === date.toDateString();
</script>

<style scoped>
    @import url('../css/calendar.css');
</style>