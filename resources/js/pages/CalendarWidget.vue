
<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>

        <div class="row g-3 h-100">
            <div class="col-lg-3 d-flex flex-column h-100">
                <LeftPanel :featuredTime="'7:30'" leaderName="ឯកឧត្តម យើត វីណែល" />
            </div>

            <div class="col-lg-9 d-flex flex-column h-100">

                <Timeline :redLineTop="62">
                    <template #morning>
                        <MeetingCard 
                        v-for="meeting in morningList" 
                        :key="meeting.id"
                        :variant="meeting.color"
                        :time="meeting.time"
                        :title="meeting.title"
                        :desc="meeting.desc"
                        />
                    </template>

                    <template #afternoon>
                        <MeetingCard 
                        v-for="meeting in afternoonList" 
                        :key="meeting.id"
                        :variant="meeting.color"
                        :time="meeting.time"
                        :title="meeting.title"
                        :desc="meeting.desc"
                        />
                    </template>
                </Timeline>

                <div class="calendar-card card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
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
                        <div v-for="day in monthDays" :key="day.dateString" 
                            class="day-cell" @click="handleDateSelection(day.dateObj)">
                            <div class="day-number" :class="{ 'today-active': day.isToday, 'selected-active': isSelected(day.dateObj) }">
                            {{ day.date }}
                            </div>
                            <div class="event-indicator-container">
                            <span v-for="e in day.events.slice(0, 3)" :key="e.id" class="dot" :class="e.dotColor"></span>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div v-else class="timeline-container p-4 fade-in overflow-auto" style="max-height: 600px;">
                        <div v-for="slot in timelineData" :key="slot.dateString" class="mb-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="date-icon shadow-sm">
                            <div class="month text-uppercase">{{ slot.monthShort }}</div>
                            <div class="day">{{ slot.dayNumber }}</div>
                            </div>
                            <div>
                            <h6 class="mb-0 fw-bold khmer-font">{{ slot.dayName }}</h6>
                            <span class="badge bg-light text-primary border rounded-pill small">{{ slot.events.length }} កិច្ចប្រជុំ</span>
                            </div>
                        </div>

                        <div v-if="slot.events.length === 0" class="empty-box khmer-font">
                            មិនមានកិច្ចប្រជុំគ្រោងទុកសម្រាប់ថ្ងៃនេះទេ
                        </div>

                        <div v-for="event in slot.events" :key="event.id" 
                            class="event-card mb-3 p-3 border-start border-4 rounded-3 shadow-sm"
                            :class="event.theme">
                            <div class="d-flex justify-content-between">
                            <div class="small text-muted fw-bold"><i class="bi bi-clock me-1"></i> {{ event.time }}</div>
                            <span class="badge bg-white text-dark border rounded-pill">{{ event.locationType }}</span>
                            </div>
                            <h6 class="khmer-font fw-bold mt-2 mb-0">{{ event.title }}</h6>
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
import { ref, computed } from 'vue'
import DashboardLayout from '../components/layouts/DashboardLayout.vue'
import HeaderBar from '@/components/HeaderBar.vue'
import LeftPanel from '@/components/LeftPanel.vue'

// --- State Management ---
const currentView = ref('month')
const referenceDate = ref(new Date())
const daysOfWeek = ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍']
const viewOptions = [
  { id: 'today', label: 'ថ្ងៃនេះ' },
  { id: 'week', label: 'សប្តាហ៍' },
  { id: 'month', label: 'ខែ' }
]

// --- Mock API Data ---
const mockEvents = [
  { id: 101, date: new Date().toISOString().split('T')[0], title: 'ប្រជុំក្រុមបច្ចេកទេសប្រចាំសប្តាហ៍', time: '09:00 - 10:30', theme: 'border-primary bg-primary-subtle', dotColor: 'bg-primary', locationType: 'Zoom' },
  { id: 102, date: new Date().toISOString().split('T')[0], title: 'ពិនិត្យរបាយការណ៍ហិរញ្ញវត្ថុ', time: '14:00 - 15:30', theme: 'border-danger bg-danger-subtle', dotColor: 'bg-danger', locationType: 'Office' }
]

// --- Computed Values ---
const viewTitle = computed(() => {
  return referenceDate.value.toLocaleDateString('km-KH', { month: 'long', year: 'numeric' })
})

const currentRangeLabel = computed(() => {
  if (currentView.value === 'today') return referenceDate.value.toLocaleDateString('km-KH', { weekday: 'long', day: 'numeric', month: 'long' })
  return 'តារាងពេលវេលាកិច្ចប្រជុំ'
})

// Logic for Month Grid
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
      events: mockEvents.filter(e => e.date === dateStr)
    }
  })
})

// Logic for Timeline (Today/Week)
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
      events: mockEvents.filter(e => e.date === dateStr)
    })
  }
  return dates
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
  currentView.value = 'today' // Standard UX: Jump to day details on click
}

const goToToday = () => {
  referenceDate.value = new Date()
}

const isSelected = (date) => referenceDate.value.toDateString() === date.toDateString()
</script>

<style scoped>
.khmer-font { font-family: 'Kantumruy Pro', sans-serif !important; }

/* Grid Layout */
.calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
.day-cell { aspect-ratio: 1/1; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; cursor: pointer; border-radius: 12px; }
.day-cell:hover:not(.empty) { background-color: #f1f5f9; }

.day-number { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: 0.3s; }
.today-active { background: #3498db; color: white !important; font-weight: bold; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
.selected-active:not(.today-active) { border: 2px solid #3498db; color: #3498db; }

/* Timeline UI */
.date-icon { width: 50px; border-radius: 10px; overflow: hidden; border: 1px solid #eee; text-align: center; background: white; }
.date-icon .month { background: #e76f51; color: white; font-size: 0.65rem; font-weight: bold; padding: 2px 0; }
.date-icon .day { font-size: 1.2rem; font-weight: bold; color: #333; }

.empty-box { padding: 20px; text-align: center; border: 2px dashed #eee; border-radius: 12px; color: #999; font-size: 0.85rem; }

/* Mini Indicators */
.event-indicator-container { display: flex; gap: 3px; position: absolute; bottom: 8px; }
.dot { width: 5px; height: 5px; border-radius: 50%; }

/* Navigation Buttons */
.nav-square-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 12px; cursor: pointer; transition: 0.2s; }
.nav-square-btn:hover { background: #f8fafc; color: #3498db; transform: translateY(-1px); }

.fade-in { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>