<template>
  <div class="side-panel d-flex flex-column gap-5 h-100">
    
    <div class="featured-card position-relative bg-white shadow border-sky">
      <div class="time-badge shadow-sm">
        <div class="d-flex align-items-start gap-1">
          <span class="time-main">{{ eventData.time }}</span>
          <span class="time-period">{{ eventData.period }}</span>
        </div>
      </div>

      <div class="card-content p-4 pt-5 mt-2">
        <div class="kh-label text-muted mb-1">ដឹកនាំដោយ</div>
        <div class="kh-leader-name mb-3">{{ eventData.leader }}</div>
        
        <div class="kh-description mb-4">
          {{ eventData.description }}
        </div>

        <div class="tag-stack d-flex flex-column gap-2">
          <div v-for="(tag, index) in eventData.tags" :key="index" class="kh-meta-tag">
            {{ tag }}
          </div>
        </div>
      </div>
    </div>

    <div class="calendar-card position-relative bg-white shadow border-0">
      
      <div class="calendar-tabs d-flex">
        <button class="cal-tab tab-red">សប្តាហ៍</button>
        <button class="cal-tab tab-orange">ខែ</button>
      </div>

      <div class="p-4 pt-4">
        <div class="text-end mb-2">
          <span class="kh-month-year">{{ currentMonth }}</span>
        </div>

        <div class="kh-calendar-grid">
          <div v-for="day in ['S','M','T','W','T','F','S']" :key="day" class="day-head">
            {{ day }}
          </div>
          
          <div v-for="empty in 3" :key="'e'+empty" class="day-cell empty"></div>

          <div 
            v-for="d in 31" 
            :key="d" 
            class="day-cell"
            :class="{ 'day-active': d === 22 }"
          >
            {{ d }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  eventData: {
    type: Object,
    default: () => ({
      time: '7:30',
      period: 'ព្រឹក',
      leader: 'ឯកឧត្តម យើត វីណែល',
      description: 'កិច្ចប្រជុំ និងពិភាក្សាផែនការថវិកាប្រចាំឆ្នាំ២០២៦',
      tags: ['VIDEO CONFERENCE', 'ជាន់ទី   បន្ទប់', 'VIDEO CONFERENCE']
    })
  }
})

const currentMonth = ref('កញ្ញា, 2025')
</script>

<style scoped>
/* BASE FONT */
.side-panel {
  font-family: 'Noto Sans Khmer', sans-serif;
  padding: 15px;
  background-color: transparent;
}

/* --- FEATURED CARD --- */
.featured-card {
  border-radius: 12px;
  border: 2px solid #63b3ed; /* Specific Blue from Image 4 */
  margin-top: 10px;
}

.time-badge {
  background-color: #f15a42; /* Vibrant red from Screenshot */
  color: white;
  position: absolute;
  top: -28px;
  left: 20px;
  padding: 8px 24px;
  border-radius: 10px;
  z-index: 10;
}

.time-main { font-size: 3rem; font-weight: 700; line-height: 1; }
.time-period { font-size: 1.1rem; margin-top: 10px; font-weight: 500; }

.kh-label { font-weight: 700; font-size: 1rem; color: #718096; }
.kh-leader-name { font-weight: 700; color: #2d3748; font-size: 1.6rem; line-height: 1.2; }
.kh-description { font-size: 1.1rem; color: #4a5568; line-height: 1.5; font-weight: 500; }

.kh-meta-tag {
  font-weight: 800;
  font-style: italic;
  font-size: 0.9rem;
  color: #a0aec0;
  text-transform: uppercase;
}

/* --- CALENDAR CARD --- */
.calendar-card {
  border-radius: 14px;
}

.calendar-tabs {
  position: absolute;
  top: -20px;
  left: 0;
}

.cal-tab {
  border: none;
  padding: 10px 35px;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
}

.tab-red {
  background-color: #d14d44; /* Darker Red from Image 2 */
  border-radius: 6px 0 0 6px;
}

.tab-orange {
  background-color: #eb7c60; /* Coral Tab from Image 2 */
  border-radius: 0 6px 6px 0;
}

.kh-month-year {
  font-weight: 700;
  font-size: 1rem;
  color: #a0aec0;
}

/* CALENDAR GRID SYSTEM */
.kh-calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
}

.day-head {
  font-weight: 700;
  font-size: 0.9rem;
  color: #a0aec0;
  padding-bottom: 12px;
  border-bottom: 1px solid #edf2f7;
  margin-bottom: 12px;
}

.day-cell {
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
  color: #a0aec0;
}

/* Date 22 Highlight */
.day-active {
  background-color: #f15a42;
  color: white !important;
  border-radius: 6px;
}

@media (max-width: 991px) {
  .calendar-card { display: none; }
}
</style>