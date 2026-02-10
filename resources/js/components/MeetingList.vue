<template>
    <div class="timeline-surface shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="timeline-blue-header px-3 py-3 text-white d-flex justify-content-between align-items-center">
            <span class="khmer-font fw-bold">
                {{ headerDate }} | សូមគោរពជម្រាបជូនថាថ្ងៃនេះមានកិច្ចប្រជុំចំនួន {{ meetings.length }}
            </span>
            <span class="badge rounded-pill bg-white bg-opacity-25 border border-white border-opacity-25 fw-bold fw-light py-2 px-3">
                {{ headerDate }}
            </span>
        </div>

        <div class="timeline-body p-5 position-relative">
            <div class="vertical-spine"></div>
            <div class="row g-0">
                <div class="col-6 pe-5">
                    <div class="column-title khmer-font text-center fw-bold text-muted mb-5">ព្រឹក</div>
                    
                    <div v-for="m in morningMeetings" :key="m.id" class="meeting-entry left-side mb-5">
                        <div class="timeline-card shadow-sm border-start border-3 rounded-3" :class="getDynamicBorder(m.colorClass)" @click="$emit('select', m)">
                            <div class="time-floating-box shadow-sm d-flex flex-column align-items-center justify-content-center" :class="m.colorClass">
                                <div class="val fw-bold lh-1 text-white fs-4">
                                    {{ m.time }}
                                </div>
                                <span class="badge bg-white bg-opacity-25 rounded-1 fw-normal px-2 py-1 small lh-1 khmer-font mt-2">
                                    {{ m.period }}
                                </span>
                            </div>
                            
                            <div class="card-body ps-5 py-3">
                                <h6 class="khmer-font fw-bold text-dark mb-1">{{ m.title }}</h6>
                                <p class="khmer-font text-muted x-small mb-3">{{ m.description }}</p>
                                <div :class="['tag-video-conf khmer-font', m.tagClass]">
                                    VIDEO CONFERENCE
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 ps-5">
            
                <div class="column-title khmer-font text-center fw-bold text-muted mb-5">រសៀល</div>
                    <div v-for="(m, idx) in afternoonMeetings" :key="m.id" class="meeting-entry right-side mb-5" :style="{ marginTop: idx === 0 ? '70px' : '0px' }" >
                        <div class="timeline-card shadow-sm border-start border-3 rounded-3" :class="getDynamicBorder(m.colorClass)" @click="$emit('select', m)">
                            <div class="time-floating-box shadow-sm d-flex flex-column align-items-center justify-content-center" :class="m.colorClass">
                                <div class="val fw-bold lh-1 text-white fs-4">
                                    {{ m.time }}
                                </div>
                                <span class="badge bg-white bg-opacity-25 rounded-1 fw-normal px-2 py-1 small lh-1 khmer-font mt-2">
                                    {{ m.period }}
                                </span>
                            </div>
                            
                            <div class="card-body ps-5 py-3">
                                <h6 class="khmer-font fw-bold text-dark mb-1">{{ m.title }}</h6>
                                <p class="khmer-font text-muted x-small mb-3">{{ m.description }}</p>
                                <div :class="['tag-video-conf khmer-font', m.tagClass]">
                                    VIDEO CONFERENCE
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { MeetingServices } from '@/services/MeetingServices';

    const props = defineProps({
        headerDate: { 
            type: String, 
            default: () => {
                const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
                const now = new Date();
                const day = now.getDate();
                const month = months[now.getMonth()];
                const year = now.getFullYear();
                const toKhmerNum = (n) => String(n).replace(/[0-9]/g, d => "០១២៣៤៥៦៧៨៩"[d]);

                return `${toKhmerNum(day)} ${month} ${toKhmerNum(year)}`;
            }
        },
        meetings: { 
            type: Array, 
            default: () => [] 
        }
    });

    const meetings = ref([]);
    const isLoading = ref(false);

    const getTodayDate = () => {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    };

    const fetchAllMeetings = async () => {
        isLoading.value = true;
        try {
            const data = await MeetingServices.getMeetingsByDate(getTodayDate());
            meetings.value = data;
        } catch (error) {
            console.error("Error fetching meetings:", error);
        } finally {
            isLoading.value = false;
        }
    };

    const morningMeetings = computed(() => meetings.value.filter(m => m.session === 'morning'));
    const afternoonMeetings = computed(() => meetings.value.filter(m => m.session === 'afternoon'));

    const getDynamicBorder = (colorClass) => {
        const map = {
            'bg-coral': 'border-danger',
            'bg-orange': 'border-warning',
            'bg-success': 'border-success',
            'bg-sky': 'border-primary'
        };
        return map[colorClass] || 'border-secondary';
    };

    onMounted(fetchAllMeetings); 
</script>

<style scoped>
    @import url('../assets/css/style.css');
    @import url('../css/meeting-list.css');
</style>