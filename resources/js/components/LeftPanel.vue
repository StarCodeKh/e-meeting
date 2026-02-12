<template>
	<div class="side-panel-wrapper">
		<div class="side-panel-container h-100">
		
			<div class="featured-card shadow-sm mb-4">
				<Transition name="slide-fade" mode="out-in">
					<div v-if="selectedDayMeetings.length > 0" :key="currentSlideIndex">
						<div class="meeting-content">
							<div class="time-header" :class="activeMeeting.colorClass || 'bg-primary'">
								<div class="d-flex justify-content-between align-items-center">
									<div class="d-flex align-items-center gap-2 gap-sm-3">
										<h1 class="display-time text-white mb-0">{{ activeMeeting.time }}</h1>
										<div v-if="selectedDayMeetings.length > 1" class="slide-indicators d-flex gap-1">
											<span v-for="(m, idx) in selectedDayMeetings" :key="idx" :class="['indicator-dot', { 'active': idx === currentSlideIndex }]"></span>
										</div>
									</div>
									<span class="badge bg-white bg-opacity-25 rounded-pill px-2 px-sm-3 py-1 small khmer-font">
										{{ activeMeeting.period }}
									</span>
								</div>
							</div>

							<div class="card-body p-3 p-sm-4 bg-white rounded-bottom-4">
								<h6 class="khmer-font fw-bold text-muted small mb-1">ដឹកនាំដោយ</h6>
								<h5 class="khmer-font leader-name fw-bold mb-3 text-dark">{{ activeMeeting.leader }}</h5>
								<p class="khmer-font description-text text-muted mb-4 small line-clamp-3"> {{ activeMeeting.description }} </p>
								<div class="meta-info mb-4 d-flex flex-column gap-2">
									<div class="meta-item small text-muted font-khmer">
										<i class="bi bi-geo-alt me-2 text-primary"></i> {{ activeMeeting.location }}
									</div>
								</div>

								<a v-if="activeMeeting.link" :href="activeMeeting.link" target="_blank" 
									class="btn btn-conference w-100 fw-bold py-2 khmer-font rounded-3">
									<i class="bi bi-camera-video me-2"></i> VIDEO CONFERENCE
								</a>
							</div>
						</div>
					</div>

					<div v-else class="empty-state p-5 text-center bg-white rounded-4 border border-dashed">
						<i class="bi bi-calendar2-x fs-1 text-light mb-3 d-block"></i>
						<p class="khmer-font text-muted small">មិនមានកិច្ចប្រជុំទេ</p>
					</div>
				</Transition>
			</div>

			<div class="calendar-card shadow-sm bg-white p-3 p-sm-4 rounded-4 border-0">
				<div class="calendar-nav d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-3">
					<div class="khmer-font fw-bold fs-5 text-dark view-title">
						{{ viewTitleKhmer }}
					</div>

					<div class="d-flex gap-2">
						<button class="nav-btn" @click="navigate(-1)"><i class="bi bi-chevron-left"></i></button>
						<button class="nav-btn" @click="navigate(1)"><i class="bi bi-chevron-right"></i></button>
					</div>
				</div>

				<div class="view-switcher d-flex bg-light p-1 justify-content-center rounded-3 border mb-3">
					<button v-for="view in ['week', 'month']" 
							:key="view" 
							@click="currentView = view" 
							:class="['btn btn-sm rounded-2 px-3 px-sm-4 khmer-font transition-all', currentView === view ? 'btn-primary text-white shadow-sm' : 'border-0 text-muted']">
						{{ view === 'week' ? 'សប្តាហ៍' : 'ខែ' }}
					</button>
				</div>

				<div class="calendar-grid">
					<div v-for="day in daysOfWeekKhmer" :key="day" class="grid-header khmer-font text-muted fw-bold text-center">
						{{ day }}
					</div>
					
					<template v-if="currentView === 'month'">
						<div v-for="n in paddingDays" :key="'p'+n" class="grid-cell empty"></div>
					</template>
					
					<div v-for="day in displayDays" 
						:key="day.dateString" 
						class="grid-cell transition-all" 
						:class="{ 'is-today': day.isToday, 'is-selected': isSelected(day.dateObj) }" 
						@click="handleDateClick(day)">
						
						<span class="day-num">{{ toKhmerNum(day.date) }}</span>
						
						<div class="dot-container">
							<span v-for="e in day.events.slice(0, 3)" 
								:key="e.id" 
								:class="['status-dot', e.colorClass]"></span>
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

    // --- State ---
    const currentView = ref('month')
    const referenceDate = ref(new Date()) // ថ្ងៃបច្ចុប្បន្ន
    const meetings = ref([])
    const currentSlideIndex = ref(0)
    let slideInterval = null

    // --- Khmer Localization Constants ---
    const daysOfWeekKhmer = ['អា', 'ច', 'អ', 'ព', 'ព្រ', 'សុ', 'ស']
    const khmerMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ']

    // --- Helper Functions ---
    // បម្លែងលេខឡាតាំងទៅជាលេខខ្មែរ (Standard សម្រាប់ UI ខ្មែរ)
    const toKhmerNum = (num) => {
        if (num === null || num === undefined) return ''
        const khmerNums = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩']
        return num.toString().replace(/\d/g, (d) => khmerNums[d])
    }

    // --- Computed Properties ---
    // ចំណងជើងខែ/ឆ្នាំ ជាខ្មែរ (Dynamic)
    const viewTitleKhmer = computed(() => {
        const month = khmerMonths[referenceDate.value.getMonth()]
        const year = toKhmerNum(referenceDate.value.getFullYear())
        return `ខែ${month} ឆ្នាំ${year}`
    })

    // កិច្ចប្រជុំសម្រាប់ថ្ងៃដែលបានជ្រើសរើស (ប្រើសម្រាប់ Featured Card)
    const selectedDayMeetings = computed(() => {
        const dateStr = referenceDate.value.toLocaleDateString('en-CA')
        return meetings.value.filter(m => m.date === dateStr)
    })

    const activeMeeting = computed(() => selectedDayMeetings.value[currentSlideIndex.value] || null)

    // --- API & Lifecycle ---
    const fetchMeetings = async () => {
        const month = referenceDate.value.getMonth() + 1
        const year = referenceDate.value.getFullYear()
        try {
            meetings.value = await MeetingCalendar.getMeetingsByMonth(month, year)
        } catch (error) {
            console.error("Error fetching meetings:", error)
        }
    }

    onMounted(() => {
        fetchMeetings()
        startAutoSlide()
    })

    onUnmounted(() => stopAutoSlide())

    // --- Slider Logic ---
    const startAutoSlide = () => {
        stopAutoSlide()
        slideInterval = setInterval(() => {
            if (selectedDayMeetings.value.length > 1) {
                currentSlideIndex.value = (currentSlideIndex.value + 1) % selectedDayMeetings.value.length
            }
        }, 5000)
    }

    const stopAutoSlide = () => {
        if (slideInterval) clearInterval(slideInterval)
    }

    // ចាប់យកការផ្លាស់ប្តូរថ្ងៃ ដើម្បី Update កិច្ចប្រជុំក្នុង API
    watch(referenceDate, (newDate, oldDate) => {
        currentSlideIndex.value = 0
        // Update API តែនៅពេលប្តូរខែ ឬឆ្នាំប៉ុណ្ណោះ ដើម្បីកាត់បន្ថយការហៅ API ច្រើនដង
        if (newDate.getMonth() !== oldDate.getMonth() || newDate.getFullYear() !== oldDate.getFullYear()) {
            fetchMeetings()
        }
    })

    // --- Calendar Navigation & Grid Logic ---
    const generateDayObject = (dateObj) => {
        const dateStr = dateObj.toLocaleDateString('en-CA')
        return {
            date: dateObj.getDate(), // លេខថ្ងៃធម្មតា (បម្លែងជាខ្មែរតាមក្រោយក្នុង Template)
            dateObj: new Date(dateObj),
            dateString: dateStr,
            isToday: new Date().toDateString() === dateObj.toDateString(),
            events: meetings.value.filter(e => e.date === dateStr)
        }
    }

    const displayDays = computed(() => {
        if (currentView.value === 'month') return monthDays.value
        
        // Week View Logic
        const start = new Date(referenceDate.value)
        start.setDate(referenceDate.value.getDate() - referenceDate.value.getDay())
        return Array.from({ length: 7 }, (_, i) => {
            const d = new Date(start)
            d.setDate(start.getDate() + i)
            return generateDayObject(d)
        })
    })

    const monthDays = computed(() => {
        const y = referenceDate.value.getFullYear()
        const m = referenceDate.value.getMonth()
        const last = new Date(y, m + 1, 0).getDate()
        return Array.from({ length: last }, (_, i) => generateDayObject(new Date(y, m, i + 1)))
    })

    const paddingDays = computed(() => {
        return new Date(referenceDate.value.getFullYear(), referenceDate.value.getMonth(), 1).getDay()
    })

    const navigate = (step) => {
        const d = new Date(referenceDate.value)
        if (currentView.value === 'month') {
            d.setMonth(d.getMonth() + step)
        } else {
            d.setDate(d.getDate() + (step * 7))
        }
        referenceDate.value = d
    }

    const handleDateClick = (day) => {
        referenceDate.value = day.dateObj
    }

    const isSelected = (date) => referenceDate.value.toDateString() === date.toDateString()
</script>

<style scoped>
	 @import url('../css/left-panel.css');
</style>