<template>
  <div class="timeline-surface shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="timeline-blue-header px-4 py-2 text-white d-flex justify-content-between align-items-center">
      <span class="khmer-font fw-bold">
        {{ headerDate }} | សូមគោរពជម្រាបជូនថាថ្ងៃនេះមានកិច្ចប្រជុំចំនួន {{ meetings.length }}
      </span>
      <span class="khmer-font small opacity-75">{{ headerDate }}</span>
    </div>

    <div class="timeline-body p-4 position-relative">
      <div class="vertical-spine"></div>
      
      <div class="row g-0">
        <div class="col-6 pe-5">
          <div class="column-title khmer-font text-center text-muted mb-5">ព្រឹក</div>
          
          <div 
            v-for="m in morningMeetings" 
            :key="m.id" 
            class="meeting-entry left-side mb-5"
          >
            <div class="timeline-card shadow-sm border" @click="$emit('select', m)">
              <div class="time-floating-box shadow-sm" :class="m.colorClass">
                <div class="val">{{ m.time }}</div>
                <div class="p khmer-font">{{ m.period }}</div>
              </div>
              
              <div class="card-body ps-5 py-3">
                <h6 class="khmer-font fw-bold text-dark mb-1">{{ m.title }}</h6>
                <p class="khmer-font text-muted x-small mb-3">{{ m.description }}</p>
                <div class="tag-video-conf khmer-font">VIDEO CONFERENCE</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 ps-5">
          <div class="column-title khmer-font text-center text-muted mb-5">រសៀល</div>
          
          <div 
            v-for="(m, idx) in afternoonMeetings" 
            :key="m.id" 
            class="meeting-entry right-side mb-5"
            :style="{ marginTop: idx === 0 ? '70px' : '0px' }" 
          >
            <div class="timeline-card shadow-sm border" @click="$emit('select', m)">
              <div class="time-floating-box shadow-sm" :class="m.colorClass">
                <div class="val">{{ m.time }}</div>
                <div class="p khmer-font">{{ m.period }}</div>
              </div>
              
              <div class="card-body ps-5 py-3">
                <h6 class="khmer-font fw-bold text-dark mb-1">{{ m.title }}</h6>
                <p class="khmer-font text-muted x-small mb-3">{{ m.description }}</p>
                <div class="tag-video-conf khmer-font">VIDEO CONFERENCE</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

// props for dynamic API data
const props = defineProps({
  headerDate: { type: String, default: '20 ខែកុម្ភៈ' },
  meetings: { 
    type: Array, 
    default: () => [
      { id: 1, time: '8:00', period: 'ព្រឹក', session: 'morning', colorClass: 'bg-coral', title: 'ពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង', description: 'កិច្ចប្រជុំ និងពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង និងការបំពេញតាមកាលវិភាគដែលបានកំណត់។' },
      { id: 2, time: '10:00', period: 'ព្រឹក', session: 'morning', colorClass: 'bg-orange', title: 'ពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង', description: 'កិច្ចប្រជុំ និងពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង និងការបំពេញតាមកាលវិភាគដែលបានកំណត់។' },
      { id: 3, time: '2:00', period: 'រសៀល', session: 'afternoon', colorClass: 'bg-orange', title: 'ពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង', description: 'កិច្ចប្រជុំ និងពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង និងការបំពេញតាមកាលវិភាគដែលបានកំណត់។' },
      { id: 4, time: '3:30', period: 'រសៀល', session: 'afternoon', colorClass: 'bg-orange', title: 'ពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង', description: 'កិច្ចប្រជុំ និងពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង និងការបំពេញតាមកាលវិភាគដែលបានកំណត់។' },
      { id: 5, time: '4:30', period: 'រសៀល', session: 'afternoon', colorClass: 'bg-orange', title: 'ពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង', description: 'កិច្ចប្រជុំ និងពិភាក្សាសេចក្ដីសំខាន់សម្រាប់ការអភិវឌ្ឍគម្រោង និងការបំពេញតាមកាលវិភាគដែលបានកំណត់។' }
    ]
  }
});

const morningMeetings = computed(() => props.meetings.filter(m => m.session === 'morning'));
const afternoonMeetings = computed(() => props.meetings.filter(m => m.session === 'afternoon'));
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap');
.khmer-font { font-family: 'Kantumruy Pro', sans-serif !important; }

/* 1. HEADER */
.timeline-blue-header { background: #3498db; }

/* 2. CENTRAL SPINE & CONNECTORS */
.vertical-spine {
  position: absolute;
  left: 50%;
  top: 60px;
  bottom: 0;
  width: 2px;
  background: #3498db;
  transform: translateX(-50%);
  z-index: 1;
}

/* Horizontal connectors */
.timeline-card::after {
  content: "";
  position: absolute;
  top: 45px;
  width: 25px;
  height: 2px;
  background: #3498db;
}
.left-side .timeline-card::after { right: -25px; }
.right-side .timeline-card::after { left: -25px; }

/* 3. MEETING CARDS & ZOOM */
.timeline-card {
  background: white;
  border-radius: 15px;
  position: relative;
  cursor: pointer;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
  z-index: 5;
}

.timeline-card:hover {
  transform: scale(1.05); /* Zoom animation from video */
  border-color: #3498db !important;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}

/* 4. TIME BADGES */
.time-floating-box {
  position: absolute;
  left: -25px;
  top: 15px;
  width: 62px;
  height: 62px;
  border-radius: 12px;
  color: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 6;
}

.val { font-size: 1.25rem; font-weight: 700; line-height: 1; }
.bg-coral { background: #e15b44; } /* Red badge */
.bg-orange { background: #f39c12; } /* Orange badge */

/* 5. CONTENT STYLING */
.tag-video-conf {
  color: #e67e22;
  background: #fffaf0;
  border: 1px solid #fed7d7;
  display: inline-block;
  padding: 2px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
}
.x-small { font-size: 0.8rem; line-height: 1.4; }

/* 6. RESPONSIVE DESIGN */
@media (max-width: 991px) {
  .vertical-spine { display: none; }
  .col-6 { width: 100%; padding: 0 1rem !important; }
  .timeline-card::after { display: none; }
  .meeting-entry { margin-top: 0 !important; }
  .time-floating-box { left: 10px; top: -15px; height: auto; width: auto; flex-direction: row; padding: 4px 12px; gap: 8px; }
  .timeline-card .card-body { padding-top: 30px !important; }
}
</style>