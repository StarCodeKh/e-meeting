<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>

        <div class="row g-3 h-100 position-relative">
            <div v-if="loading" class="api-loader shadow-sm">
                <div class="spinner-border text-primary" role="status"></div>
                <span class="ms-2 khmer-font">កំពុងទាញទិន្នន័យ...</span>
            </div>

            <div class="col-lg-3 d-flex flex-column h-100">
                <LeftPanel :featuredTime="'7:30'" leaderName="ឯកឧត្តម យើត វីណែល" />
            </div>

            <div class="col-lg-9 d-flex flex-column h-100">
                
                <Timeline v-if="currentView !== 'month'" :redLineTop="62">
                    <template #morning>
                        <MeetingCard 
                            v-for="meeting in morningList" 
                            :key="meeting.id"
                            :variant="meeting.color_id"
                            :time="meeting.start_time + ' - ' + meeting.end_time"
                            :title="meeting.title"
                            :desc="meeting.description"
                        />
                        <div v-if="morningList.length === 0" class="text-muted small p-3 khmer-font">មិនមានកិច្ចប្រជុំពេលព្រឹក</div>
                    </template>

                    <template #afternoon>
                        <MeetingCard 
                            v-for="meeting in afternoonList" 
                            :key="meeting.id"
                            :variant="meeting.color_id"
                            :time="meeting.start_time + ' - ' + meeting.end_time"
                            :title="meeting.title"
                            :desc="meeting.description"
                        />
                        <div v-if="afternoonList.length === 0" class="text-muted small p-3 khmer-font">មិនមានកិច្ចប្រជុំពេលរសៀល</div>
                    </template>
                </Timeline>

                <div class="calendar-card card border-0 shadow-sm rounded-4 overflow-hidden bg-white mt-3">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h4 class="khmer-font fw-bold mb-0 text-dark">{{ viewTitle }}</h4>
                                <p class="text-muted small mb-0 khmer-font">{{ currentRangeLabel }}</p>
                            </div>

                            <div class="d-flex gap-1 bg-light p-1 rounded-pill border shadow-none">
                                <button v-for="v in viewOptions" :key="v.id" 
                                    class="btn btn-sm rounded-pill px-3 transition-all khmer-font" 
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
                        <div v-if="currentView === 'month'" class="fade-in">
                            <div class="calendar-grid bg-light py-2 border-top border-bottom">
                                <div v-for="day in daysOfWeek" :key="day" class="text-center small fw-bold text-muted khmer-font">
                                    {{ day }}
                                </div>
                            </div>
                            <div class="calendar-grid p-2">
                                <div v-for="n in paddingDays" :key="'p-'+n" class="day-cell empty"></div>
                                <div v-for="day in monthDays" :key="day.dateString" class="day-cell" @click="handleDateSelection(day.dateObj)">
                                    <div class="day-number" :class="{ 'today-active': day.isToday, 'selected-active': isSelected(day.dateObj) }">
                                        {{ day.date }}
                                    </div>
                                    <div class="event-indicator-container">
                                        <span v-for="e in day.events.slice(0, 3)" :key="e.id" 
                                            class="dot" 
                                            :style="{ backgroundColor: getColorHex(e.color_id) }"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="timeline-container p-4 fade-in overflow-auto" style="max-height: 600px;">
                            <div v-for="slot in timelineData" :key="slot.dateString" class="mb-5">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="date-icon shadow-sm" :class="{ 'bg-primary text-white': slot.dateString === new Date().toISOString().split('T')[0] }">
                                        <div class="month text-uppercase">{{ slot.monthShort }}</div>
                                        <div class="day fw-bold">{{ slot.dayNumber }}</div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold khmer-font">{{ slot.dayName }}</h6>
                                        <span class="badge bg-light text-primary border rounded-pill small">{{ slot.events.length }} កិច្ចប្រជុំ</span>
                                    </div>
                                </div>

                                <div v-if="slot.events.length === 0" class="empty-box khmer-font py-4 text-center border rounded-3 opacity-50">
                                    <i class="bi bi-calendar-x d-block fs-4 mb-2"></i>
                                    មិនមានកិច្ចប្រជុំគ្រោងទុកទេ
                                </div>

                                <div v-for="event in slot.events" :key="event.id" 
                                    class="event-card mb-3 p-3 border-start border-4 rounded-3 shadow-sm bg-white hover-shadow"
                                    :style="{ borderLeftColor: getColorHex(event.color_id) }">
                                    <div class="d-flex justify-content-between">
                                        <div class="small text-muted fw-bold">
                                            <i class="bi bi-clock me-1"></i> {{ event.start_time }} - {{ event.end_time }}
                                        </div>
                                        <span class="badge bg-light text-dark border rounded-pill small">
                                            {{ event.room || event.location || 'មិនកំណត់ទីតាំង' }}
                                        </span>
                                    </div>
                                    <h6 class="khmer-font fw-bold mt-2 mb-1">{{ event.title }}</h6>
                                    <p v-if="event.description" class="text-muted small mb-0 text-truncate">{{ event.description }}</p>
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
import api from '@/api/axios'

// --- State Management ---
const currentView = ref('month')
const referenceDate = ref(new Date())
const events = ref([]) 
const loading = ref(false)

const daysOfWeek = ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍']
const viewOptions = [
    { id: 'today', label: 'ថ្ងៃនេះ' },
    { id: 'week', label: 'សប្តាហ៍' },
    { id: 'month', label: 'ខែ' }
]

// --- Helper: បំប្លែង ID ពណ៌ទៅជា Class ឬ Hex ---
const getColorHex = (colorId) => {
    const colors = {
        red: '#ff6b6b',
        yellow: '#fcc419',
        green: '#51cf66',
        blue: '#3498db'
    }
    return colors[colorId] || '#718096'
}

// --- API Methods ---
const fetchEvents = async () => {
    loading.value = true
    try {
        const year = referenceDate.value.getFullYear()
        const month = referenceDate.value.getMonth() + 1
        
        const response = await api.get('schedules/calendar', {
            params: { 
                month: month.toString().padStart(2, '0'),
                year: year
            }
        })
        events.value = response.data.data 
    } catch (error) {
        console.error("Error fetching schedules:", error)
    } finally {
        loading.value = false
    }
}

watch([referenceDate, currentView], () => {
    fetchEvents()
}, { immediate: true })

// --- Computed: បែងចែកបញ្ជីសម្រាប់ Timeline (Today) ---
const todayEvents = computed(() => {
    const selectedDateStr = referenceDate.value.toISOString().split('T')[0]
    return events.value.filter(e => e.date === selectedDateStr)
})

const morningList = computed(() => {
    return todayEvents.value.filter(e => {
        const hour = parseInt(e.start_time.split(':')[0])
        return hour < 12 // មុនម៉ោង ១២ ថ្ងៃត្រង់
    })
})

const afternoonList = computed(() => {
    return todayEvents.value.filter(e => {
        const hour = parseInt(e.start_time.split(':')[0])
        return hour >= 12 // ចាប់ពីម៉ោង ១២ ថ្ងៃត្រង់
    })
})

// --- Computed: រៀបចំទិន្នន័យសម្រាប់ Calendar Grid ---
const paddingDays = computed(() => {
    const d = new Date(referenceDate.value.getFullYear(), referenceDate.value.getMonth(), 1)
    return d.getDay()
})

const monthDays = computed(() => {
    const year = referenceDate.value.getFullYear()
    const month = referenceDate.value.getMonth()
    const lastDay = new Date(year, month + 1, 0).getDate()
    
    return Array.from({ length: lastDay }, (_, i) => {
        const d = new Date(year, month, i + 1)
        const dateStr = d.toISOString().split('T')[0]
        return {
            date: i + 1,
            dateObj: d,
            dateString: dateStr,
            isToday: new Date().toDateString() === d.toDateString(),
            events: events.value.filter(e => e.date === dateStr)
        }
    })
})

// Logic for Timeline (Week View)
const timelineData = computed(() => {
    const dates = []
    const tempDate = new Date(referenceDate.value)
    
    const count = currentView.value === 'today' ? 1 : 7
    if (currentView.value === 'week') tempDate.setDate(tempDate.getDate() - tempDate.getDay())

    for (let i = 0; i < count; i++) {
        const d = new Date(tempDate)
        d.setDate(d.getDate() + i)
        const dateStr = d.toISOString().split('T')[0]
        dates.push({
            dateString: dateStr,
            dayNumber: d.getDate(),
            dayName: daysOfWeek[d.getDay()],
            monthShort: d.toLocaleDateString('en-US', { month: 'short' }),
            events: events.value.filter(e => e.date === dateStr)
        })
    }
    return dates
})

const viewTitle = computed(() => {
    return referenceDate.value.toLocaleDateString('km-KH', { month: 'long', year: 'numeric' })
})

const currentRangeLabel = computed(() => {
    if (currentView.value === 'today') return referenceDate.value.toLocaleDateString('km-KH', { weekday: 'long', day: 'numeric', month: 'long' })
    return 'តារាងពេលវេលាកិច្ចប្រជុំ'
})

// --- Methods ---
const navigate = (step) => {
    const d = new Date(referenceDate.value)
    if (currentView.value === 'month') d.setMonth(d.getMonth() + step)
    else if (currentView.value === 'week') d.setDate(d.getDate() + (step * 7))
    else d.setDate(d.getDate() + step)
    referenceDate.value = d
}

const handleDateSelection = (date) => {
    referenceDate.value = date
    currentView.value = 'today'
}

const goToToday = () => { referenceDate.value = new Date() }
const isSelected = (date) => referenceDate.value.toDateString() === date.toDateString()
</script>

<style scoped>
    @import url('../css/calendar.css');
</style>