<template>
    <div class="timeline-surface shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="timeline-blue-header px-3 py-3 text-white d-flex justify-content-between align-items-center">
            <span class="khmer-font fw-bold">
                {{ headerDate }} | សូមគោរពជម្រាបជូនថាថ្ងៃនេះមានកិច្ចប្រជុំចំនួន {{ toKhmerNum(meetings.length) }}
            </span>
            <span class="badge rounded-3 bg-white bg-opacity-25 border border-white border-opacity-25 fw-bold fw-light py-2 px-3">
                {{ headerDate }}
            </span>
        </div>

        <div class="timeline-body p-5 position-relative">
            <div class="vertical-spine"></div>
            
            <div class="row g-0">
                <div class="col-6 pe-5">
                    <div class="column-title khmer-font text-center fw-bold text-muted mb-5">ព្រឹក</div>
                    
                    <div v-if="morningMeetings.length > 0">
                        <div v-for="m in morningMeetings" :key="m.id" class="meeting-entry left-side mb-5">
                            <div class="timeline-card shadow-sm border-start border-4 rounded-3" 
                                :style="{ borderLeftColor: getPriorityColor(m.color_id) + ' !important' }"
                                @click="$emit('select', m)">
                                
                                <div class="time-floating-box shadow-sm d-flex flex-column align-items-center justify-content-center text-white" 
                                    :style="{ backgroundColor: getPriorityColor(m.color_id) }">
                                    <div class="khmer-font val lh-1 fs-4">{{ toKhmerNum(m.time) }}</div>
                                    <span class="badge bg-white bg-opacity-25 rounded-1 fw-normal px-2 py-1 small lh-1 khmer-font mt-2">{{ m.period }}</span>
                                </div>

                                <div class="card-body ps-5 py-3">
                                    <h6 class="khmer-font fw-bold text-dark mb-1">{{ m.title }}</h6>
                                    <p class="khmer-font text-muted x-small mb-3">{{ m.description }}</p>

                                    <div v-if="m.link || m.attachmentUrl" class="border-top mt-2 pt-2 d-flex justify-content-start gap-2">
                                        <a v-if="m.link && m.link !== '#'" :href="m.link" target="_blank" @click.stop class="btn btn-sm btn-light border rounded-2 action-icon">
                                            <i class="bi bi-camera-video-fill text-primary"></i>
                                        </a>
                                        <a v-if="m.attachmentUrl" :href="m.attachmentUrl" target="_blank" @click.stop class="btn btn-sm btn-light border rounded-2 action-icon">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="meeting-entry left-side mb-5">
                        <div class="timeline-card shadow-sm border-start border-3 border-secondary rounded-3 bg-light opacity-75">
                            <div class="card-body ps-4 py-4 text-center">
                                <i class="bi bi-sunrise text-muted fs-3 mb-2"></i>
                                <h6 class="khmer-font text-muted mb-0">មិនមានកិច្ចប្រជុំពេលព្រឹកឡើយ</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 ps-5">
                    <div class="column-title khmer-font text-center fw-bold text-muted mb-5">រសៀល</div>
                    
                    <div v-if="afternoonMeetings.length > 0">
                        <div v-for="(m, idx) in afternoonMeetings" :key="m.id" 
                            class="meeting-entry right-side mb-5" 
                            :style="{ marginTop: idx === 0 ? '70px' : '0px' }">
                            
                            <div class="timeline-card shadow-sm border-start border-4 rounded-3" 
                                :style="{ borderLeftColor: getPriorityColor(m.color_id) + ' !important' }"
                                @click="$emit('select', m)">
                                
                                <div class="time-floating-box shadow-sm d-flex flex-column align-items-center justify-content-center text-white" 
                                    :style="{ backgroundColor: getPriorityColor(m.color_id) }">
                                    <div class="khmer-font val lh-1 fs-4">{{ toKhmerNum(m.time) }}</div>
                                    <span class="badge bg-white bg-opacity-25 rounded-1 fw-normal px-2 py-1 small lh-1 khmer-font mt-2">{{ m.period }}</span>
                                </div>
                                
                                <div class="card-body ps-5 py-3">
                                    <h6 class="khmer-font fw-bold text-dark mb-1">{{ m.title }}</h6>
                                    <p class="khmer-font text-muted x-small mb-3">{{ m.description }}</p>

                                    <div v-if="m.link || m.attachmentUrl" class="border-top mt-2 pt-2 d-flex justify-content-start gap-2">
                                        <a v-if="m.link && m.link !== '#'" :href="m.link" target="_blank" @click.stop class="btn btn-sm btn-light border rounded-2 action-icon">
                                            <i class="bi bi-camera-video-fill text-primary"></i>
                                        </a>
                                        <a v-if="m.attachmentUrl" :href="m.attachmentUrl" target="_blank" @click.stop class="btn btn-sm btn-light border rounded-2 action-icon">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="meeting-entry right-side mb-5" style="margin-top: 70px;">
                        <div class="timeline-card shadow-sm border-start border-3 border-secondary rounded-3 bg-light opacity-75">
                            <div class="card-body ps-4 py-4 text-center">
                                <i class="bi bi-sunset text-muted fs-3 mb-2"></i>
                                <h6 class="khmer-font text-muted mb-0">មិនមានកិច្ចប្រជុំពេលរសៀលឡើយ</h6>
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
    import { getScheduleFormOptions } from '@/services/ScheduleTypes';

    const props = defineProps({
        headerDate: { type: String, default: '' }
    });

    const meetings = ref([]);
    const priorities = ref([]);
    const isLoading = ref(false);

    // --- Khmer Number Converter ---
    const toKhmerNum = (num) => {
        if (!num && num !== 0) return '';
        return String(num).replace(/[0-9]/g, d => "០១២៣៤៥៦៧៨៩"[d]);
    };

    // --- Dynamic Header Date ---
    const headerDate = computed(() => {
        if (props.headerDate) return props.headerDate;
        const months = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
        const now = new Date();
        return `ថ្ងៃទី${toKhmerNum(now.getDate())} ខែ${months[now.getMonth()]} ឆ្នាំ${toKhmerNum(now.getFullYear())}`;
    });

    // --- Dynamic Color Matching ---
    const getPriorityColor = (colorId) => {
        if (!priorities.value.length) return '#6c757d';
        const p = priorities.value.find(x => x.id == colorId || x.slug == colorId);
        if (p) {
            return p.color_hex || p.color || '#3498db';
        }
        return '#6c757d'; 
    };

    const getTodayDate = () => {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    };

    const fetchAllData = async () => {
        isLoading.value = true;
        try {
            const [meetingData, options] = await Promise.all([
                MeetingServices.getMeetingsByDate(getTodayDate()),
                getScheduleFormOptions()
            ]);
            
            priorities.value = options.colors || options.priorities || [];
            meetings.value = meetingData;
        } catch (error) {
            console.error("API Error:", error);
        } finally {
            isLoading.value = false;
        }
    };

    const morningMeetings = computed(() => meetings.value.filter(m => m.session === 'morning'));
    const afternoonMeetings = computed(() => meetings.value.filter(m => m.session === 'afternoon'));

    onMounted(fetchAllData);
</script>

<style scoped>
    @import url('../assets/css/style.css');
    @import url('../css/meeting-list.css');
</style>