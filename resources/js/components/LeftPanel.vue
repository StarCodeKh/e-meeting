<template>
  <div class="side-panel-container h-100">
    <div class="featured-card shadow-sm mb-4">
      <div class="time-header">
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="display-time mb-0">{{ eventData.time }}</h1>
          <span class="period-badge">{{ eventData.period }}</span>
        </div>
      </div>

      <div class="card-body p-3 pt-4">
        <h6 class="khmer-font fw-bold text-dark mb-1">ដឹកនាំដោយ</h6>
        <h5 class="khmer-font leader-name mb-3">{{ eventData.leader }}</h5>
        
        <p class="khmer-font description-text text-muted mb-4">
          {{ eventData.description }}
        </p>

        <div class="meta-info mb-3">
          <div class="meta-item text-uppercase">VIDEO CONFERENCE</div>
          <div class="meta-item text-uppercase">ជាន់ទី.... បន្ទប់ .....</div>
        </div>

        <button class="btn-conference w-100 fw-bold py-2 khmer-font">
          VIDEO CONFERENCE
        </button>
      </div>
    </div>

    <div class="calendar-card shadow-sm bg-white p-3">
      <div class="calendar-nav d-flex justify-content-between align-items-center mb-3">
        <div class="tab-switcher d-flex shadow-sm">
          <div class="tab-item tab-red">សប្តាហ៍</div>
          <div class="tab-item tab-orange">ខែ</div>
        </div>
        <div class="khmer-font fw-bold month-label">
          {{ monthNamesKhmer[currentMonth] }}, {{ currentYear }}
        </div>
      </div>

      <div class="calendar-grid">
        <div v-for="day in weekDaysKhmer" :key="day" class="grid-header khmer-font">
          {{ day }}
        </div>
        
        <div v-for="blank in firstDayOffset" :key="'b'+blank" class="grid-cell empty"></div>
        
        <div 
          v-for="date in daysInMonth" 
          :key="date" 
          class="grid-cell"
          :class="getSpecialDateClass(date)"
        >
          {{ date }}
          <span v-if="hasDot(date)" :class="['status-dot', getDotColor(date)]"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// 1. DYNAMIC DATA FOR MEETING
const eventData = ref({
  time: '8:25',
  period: 'ព្រឹក',
  leader: 'ឯកឧត្តម យើត វីណែល',
  description: 'កិច្ចប្រជុំ និងពិភាក្សាសេចក្ដីសំខាន់ សម្រាប់ការអភិវឌ្ឍគម្រោង និងការបំពេញតាមកាលវិភាគដែលបានកំណត់។',
})

// 2. DYNAMIC CALENDAR LOGIC
const now = new Date()
const currentYear = ref(now.getFullYear())
const currentMonth = ref(now.getMonth()) // 0-indexed

const monthNamesKhmer = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ']
const weekDaysKhmer = ['អ', 'ច', 'អ', 'ព', 'ស', 'ស', 'អ']

// Calculate days in month
const daysInMonth = computed(() => {
  return new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
})

// Calculate which day of the week the 1st starts on
const firstDayOffset = computed(() => {
  return new Date(currentYear.value, currentMonth.value, 1).getDay()
})

// Class Logic for specific dates (matching screenshot)
const getSpecialDateClass = (date) => {
  if (date === 20) return 'is-today' // Based on screenshot
  return ''
}

const hasDot = (date) => [21, 22, 23].includes(date)
const getDotColor = (date) => {
  if (date === 21) return 'dot-blue'
  if (date === 22) return 'dot-orange'
  if (date === 23) return 'dot-red'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap');

.side-panel-container {
  font-family: 'Kantumruy Pro', sans-serif;
  max-width: 380px;
  width: 100%;
}

/* FEATURED CARD */
.featured-card {
  background: white;
  border: 1.5px solid #a0c4ff;
  border-radius: 18px;
  overflow: visible;
  position: relative;
}

.time-header {
  background: #e15b44; /* Vivid Red-Orange from Image */
  color: white;
  margin: 12px;
  padding: 8px 18px;
  border-radius: 12px;
}

.display-time { font-size: 3rem; font-weight: 700; letter-spacing: -2px; }
.period-badge { background: rgba(255,255,255,0.2); padding: 2px 10px; border-radius: 6px; font-size: 0.9rem; }

.leader-name { color: #2d3748; line-height: 1.3; }
.description-text { font-size: 0.88rem; line-height: 1.5; }

.meta-info { font-size: 0.75rem; color: #718096; font-weight: 700; }
.btn-conference {
  background: #f0f7ff;
  color: #3182ce;
  border: none;
  border-radius: 10px;
  transition: 0.2s ease;
}
.btn-conference:hover { background: #e1efff; transform: translateY(-1px); }

/* CALENDAR STYLES */
.calendar-card { border-radius: 18px; }
.tab-switcher { border-radius: 8px; overflow: hidden; }
.tab-item { padding: 4px 12px; font-size: 0.8rem; color: white; font-weight: 700; cursor: pointer; }
.tab-red { background: #d14d44; }
.tab-orange { background: #eb7c60; }

.calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.grid-header { 
  text-align: center; color: #a0aec0; font-size: 0.8rem; 
  padding-bottom: 8px; border-bottom: 1px solid #edf2f7; margin-bottom: 8px;
}

.grid-cell {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 700;
  color: #4a5568;
  position: relative;
  cursor: pointer;
  border-radius: 8px;
}

.is-today { background: #e15b44; color: white !important; }

/* DOTS */
.status-dot {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 5px;
  height: 5px;
  border-radius: 50%;
}
.dot-blue { background: #4299e1; }
.dot-orange { background: #ed8936; }
.dot-red { background: #f56565; }

/* RESPONSIVE SMOOTHING */
@media (max-width: 992px) {
  .side-panel-container { max-width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
}

@media (max-width: 768px) {
  .side-panel-container { display: flex; flex-direction: column; }
}
</style>