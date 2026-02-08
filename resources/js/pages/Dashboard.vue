<template>
  <DashboardLayout>
    <template #header>
      <HeaderBar />
    </template>

    <div class="row g-3 h-100">
      
      <div class="col-lg-3 d-flex flex-column h-100">
        <LeftPanel 
          :featuredTime="'7:30'" 
          leaderName="ឯកឧត្តម យើត វីណែល" 
        />
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
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '../components/layouts/DashboardLayout.vue'
import HeaderBar from '@/components/HeaderBar.vue'
import LeftPanel from '@/components/LeftPanel.vue'
import Timeline from '@/components/Timeline.vue'
import MeetingCard from '@/components/MeetingCard.vue'

// DYNAMIC DATA
const meetings = ref([
  { id: 1, time: '8:00 ព្រឹក', type: 'morning', color: 'red', title: 'កិច្ចប្រជុំផែនការថវិកាប្រចាំឆ្នាំ២០២៦', desc: 'ក្រោមអធិបតីភាពដ៏ខ្ពង់ខ្ពស់...' },
  { id: 2, time: '10:00 ព្រឹក', type: 'morning', color: 'yellow', title: 'ពិភាក្សាបច្ចេកទេសប្រព័ន្ធ', desc: 'ការរៀបចំប្រព័ន្ធគ្រប់គ្រង...' },
  { id: 3, time: '2:00 រសៀល', type: 'afternoon', color: 'yellow', title: 'កិច្ចប្រជុំផ្ទៃក្នុងប្រចាំខែ', desc: 'ត្រួតពិនិត្យវឌ្ឍនភាពការងារ...' }
])

const totalMeetings = computed(() => meetings.value.length)
const morningList = computed(() => meetings.value.filter(m => m.type === 'morning'))
const afternoonList = computed(() => meetings.value.filter(m => m.type === 'afternoon'))
</script>

<style scoped>
	.banner-blue-gradient {
	background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
	height: 75px;
	border-radius: 12px;
	}
	.kh-light { font-weight: 300; }
</style>