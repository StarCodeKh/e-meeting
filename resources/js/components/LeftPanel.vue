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
										<h1 class="display-time mb-0">{{ activeMeeting.time }}</h1>
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

			<div class="calendar-card shadow-sm bg-white p-3 p-sm-4 rounded-4">
				<div class="calendar-nav d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
					<div class="view-switcher d-flex bg-light p-1 rounded-pill border">
						<button v-for="view in ['week', 'month']" :key="view"
								@click="currentView = view"
								:class="['btn btn-sm rounded-pill px-3 px-sm-4 khmer-font transition-all', 
										currentView === view ? 'btn-primary text-white shadow-sm' : 'border-0 text-muted']">
						{{ view === 'week' ? 'សប្តាហ៍' : 'ខែ' }}
						</button>
					</div>
					
					<div class="khmer-font fw-bold fs-6 text-dark view-title">
						{{ viewTitle }}
					</div>

					<div class="d-flex gap-2">
						<button class="nav-btn" @click="navigate(-1)"><i class="bi bi-chevron-left"></i></button>
						<button class="nav-btn" @click="navigate(1)"><i class="bi bi-chevron-right"></i></button>
					</div>
					</div>

					<div class="calendar-grid">
					<div v-for="day in daysOfWeek" :key="day" class="grid-header khmer-font text-muted fw-bold">
						{{ day }}
					</div>
					
					<template v-if="currentView === 'month'">
						<div v-for="n in paddingDays" :key="'p'+n" class="grid-cell empty"></div>
					</template>
					
					<div v-for="day in displayDays" :key="day.dateString" class="grid-cell transition-all" :class="{ 'is-today': day.isToday, 'is-selected': isSelected(day.dateObj) }" @click="handleDateClick(day)">
						<span class="day-num">{{ day.date }}</span>
						<div class="dot-container">
							<span v-for="e in day.events.slice(0, 3)" :key="e.id" :class="['status-dot', e.colorClass]"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import {
		ref
		, computed
		, onMounted
		, watch
		, onUnmounted
	} from 'vue'
	import {
		MeetingCalendar
	} from '@/services/MeetingCalendar'

	const currentView = ref('month')
	const referenceDate = ref(new Date())
	const meetings = ref([])
	const daysOfWeek = ['អា', 'ច', 'អ', 'ព', 'ព្រ', 'សុ', 'ស']
	const currentSlideIndex = ref(0)
	let slideInterval = null

	const fetchMeetings = async () => {
		const month = referenceDate.value.getMonth() + 1
		const year = referenceDate.value.getFullYear()
		meetings.value = await MeetingCalendar.getMeetingsByMonth(month, year)
	}

	onMounted(() => {
		fetchMeetings();
		startAutoSlide();
	})
	onUnmounted(() => stopAutoSlide())

	const selectedDayMeetings = computed(() => {
		const dateStr = referenceDate.value.toLocaleDateString('en-CA')
		return meetings.value.filter(m => m.date === dateStr)
	})

	const activeMeeting = computed(() => selectedDayMeetings.value[currentSlideIndex.value] || null)

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

	watch(referenceDate, () => {
		currentSlideIndex.value = 0
		fetchMeetings()
	})

	const displayDays = computed(() => {
		if (currentView.value === 'month') return monthDays.value
		const start = new Date(referenceDate.value)
		start.setDate(referenceDate.value.getDate() - referenceDate.value.getDay())
		return Array.from({
			length: 7
		}, (_, i) => {
			const d = new Date(start);
			d.setDate(start.getDate() + i)
			return generateDayObject(d)
		})
	})

	const monthDays = computed(() => {
		const y = referenceDate.value.getFullYear()
			, m = referenceDate.value.getMonth()
		const last = new Date(y, m + 1, 0).getDate()
		return Array.from({
			length: last
		}, (_, i) => generateDayObject(new Date(y, m, i + 1)))
	})

	const generateDayObject = (dateObj) => {
		const dateStr = dateObj.toLocaleDateString('en-CA')
		return {
			date: dateObj.getDate()
			, dateObj: new Date(dateObj)
			, dateString: dateStr
			, isToday: new Date().toDateString() === dateObj.toDateString()
			, events: meetings.value.filter(e => e.date === dateStr)
		}
	}

	const viewTitle = computed(() => referenceDate.value.toLocaleDateString('km-KH', {
		month: 'long'
		, year: 'numeric'
	}))
	const paddingDays = computed(() => new Date(referenceDate.value.getFullYear(), referenceDate.value.getMonth(), 1).getDay())
	const navigate = (step) => {
		const d = new Date(referenceDate.value)
		if (currentView.value === 'month') d.setMonth(d.getMonth() + step)
		else d.setDate(d.getDate() + (step * 7))
		referenceDate.value = d
	}
	const handleDateClick = (day) => {
		referenceDate.value = day.dateObj
	}
	const isSelected = (date) => referenceDate.value.toDateString() === date.toDateString()
</script>

<style scoped>
	/* WRAPPER FOR RESPONSIVE */
	.side-panel-wrapper {
		width: 100%;
		max-width: 400px;
		margin: 0 auto;
	}

	.side-panel-container {
		font-family: 'Kantumruy Pro', sans-serif;
		width: 100%;
	}

	/* FEATURED CARD */
	.featured-card {
		border-radius: 20px;
		background: white;
		overflow: hidden;
	}

	.time-header {
		margin: 10px;
		padding: 15px;
		border-radius: 15px;
	}

	.display-time {
		font-size: calc(1.8rem + 1vw);
		font-weight: 800;
		line-height: 1;
	}

	.indicator-dot {
		width: 5px;
		height: 5px;
		background: rgba(255, 255, 255, 0.3);
		border-radius: 50%;
	}

	.indicator-dot.active {
		width: 12px;
		background: white;
		border-radius: 4px;
	}

	.btn-conference {
		background: #f0f7ff;
		color: #3182ce;
		border: none;
		font-size: 0.9rem;
	}

	/* RESPONSIVE CALENDAR GRID */
	.calendar-grid {
		display: grid;
		grid-template-columns: repeat(7, 1fr);
		gap: 2px;
		width: 100%;
	}

	.grid-header {
		font-size: 0.7rem;
		padding: 8px 0;
		text-align: center;
		border-bottom: 1px solid #eee;
	}

	.grid-cell {
		aspect-ratio: 1 / 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		position: relative;
		cursor: pointer;
		border-radius: 8px;
		font-size: 0.85rem;
	}

	.day-num {
		font-weight: 700;
		z-index: 1;
	}

	.is-today {
		background: #e15b44 !important;
		color: white !important;
	}

	.is-selected:not(.is-today) {
		background: #ebf8ff;
		border: 1px solid #bee3f8;
		color: #3182ce;
	}

	.dot-container {
		display: flex;
		gap: 2px;
		position: absolute;
		bottom: 4px;
		justify-content: center;
		width: 100%;
	}

	.status-dot {
		width: 4px;
		height: 4px;
		border-radius: 50%;
	}

	/* API COLORS */
	.bg-coral {
		background: #f56565 !important;
	}

	.bg-orange {
		background: #ed8936 !important;
	}

	.bg-success {
		background: #48bb78 !important;
	}

	.bg-primary {
		background: #4299e1 !important;
	}

	.nav-btn {
		width: 30px;
		height: 30px;
		border-radius: 8px;
		border: 1px solid #ddd;
		background: #fff;
	}

	/* MEDIA QUERIES FOR MOBILE */
	@media (max-width: 576px) {
		.side-panel-wrapper {
			max-width: 100%;
			padding: 10px;
		}

		.display-time {
			font-size: 2.2rem;
		}

		.grid-cell {
			font-size: 0.75rem;
		}

		.view-title {
			font-size: 0.9rem;
			order: -1;
			width: 100%;
			text-align: center;
			margin-bottom: 5px;
		}

		.calendar-nav {
			gap: 10px;
		}
	}

	.line-clamp-3 {
		display: -webkit-box;
		-webkit-line-clamp: 3;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	/* ANIMATION */
	.slide-fade-enter-active,
	.slide-fade-leave-active {
		transition: all 0.4s ease;
	}

	.slide-fade-enter-from {
		opacity: 0;
		transform: translateX(20px);
	}

	.slide-fade-leave-to {
		opacity: 0;
		transform: translateX(-20px);
	}
</style>