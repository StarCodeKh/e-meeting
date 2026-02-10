<template>
    <Transition name="modal-bounce">
        <div v-if="modelValue" class="modal-overlay" @click.self="closeModal">
            <div class="custom-modal shadow-lg" :style="{ borderColor: activeTheme }">
                
                <div class="modal-tabs">
                    <button v-for="tab in TABS" :key="tab.id" class="tab-item khmer-font" 
                        :style="form.type === tab.id ? { background: tab.theme, color: 'white' } : {}" 
                        @click="form.type = tab.id">
                        <i :class="tab.icon" class="me-1"></i> {{ tab.label }}
                    </button>
                </div>

                <form @submit.prevent="handleSave" class="modal-inner">
                    <div class="title-section mb-4">
                        <input v-model="form.title" type="text" placeholder="បញ្ចូលចំណងជើង..." class="title-input khmer-font" :style="{ borderBottomColor: activeTheme }" required />
                    </div>

                    <div class="form-content">
                        <div class="form-row">
                            <i class="bi bi-calendar3 icon-gray"></i>
                            <div class="d-flex flex-column flex-md-row gap-2 w-100">
                                <input v-model="form.date" type="date" class="pill-input flex-grow-1" />
                                <div class="time-container">
                                    <input v-model="form.start_time" type="time" class="time-input-inline" />
                                    <span class="mx-1 text-muted">-</span>
                                    <input v-model="form.end_time" type="time" class="time-input-inline" /> 
                                </div>
                            </div>
                        </div>

                        <div class="form-row" v-if="form.type !== 'task'">
                            <i class="bi bi-people icon-gray"></i>
                            <input v-model="form.participants" type="text" placeholder="អ្នកចូលរួម" class="pill-input w-100" />
                        </div>

                        <div class="form-row">
                            <i class="bi bi-geo-alt icon-gray"></i>
                            <div class="pill-group w-100">
                                <input v-model="form.location" type="text" placeholder="ទីតាំង" class="pill-input flex-grow-1" />
                                <input v-if="form.type === 'meeting'" v-model="form.room" type="text" placeholder="បន្ទប់" class="pill-input w-25" />
                            </div>
                        </div>

                        <div class="form-row">
                            <i class="bi bi-globe2 icon-gray"></i>
                            <input v-model="form.link" type="url" placeholder="លីងតំណភ្ជាប់..." class="pill-input w-100" />
                        </div>

                        <div class="mt-4">
                            <label class="khmer-font small text-muted mb-2 d-block">កម្រិតអាទិភាព</label>
                            <div class="color-row-container">
                                <div v-for="color in COLOR_OPTIONS" :key="color.id" class="color-option" @click="form.color = color.id">
                                    <div class="color-bubble" :style="{ backgroundColor: color.hex }" :class="{ 'selected': form.color === color.id }">
                                        <i v-if="form.color === color.id" class="bi bi-check-lg text-white"></i>
                                    </div>
                                    <span class="color-text khmer-font">{{ color.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-custom d-flex justify-content-between mt-4 pt-3 border-top">
                        <button type="button" class="btn-cancel khmer-font" @click="closeModal">បោះបង់</button>
                        <button type="submit" class="btn-save-dynamic khmer-font" :disabled="loading" :style="{ background: activeGradient }">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<script setup>
    import { ref, reactive, computed, watch } from 'vue'
    import api from '@/api/axios'
    import { alertStore } from '@/stores/alert'

    const props = defineProps({ modelValue: Boolean })
    const emit = defineEmits(['update:modelValue', 'refresh'])
    const loading = ref(false)

    // ១. កំណត់តម្លៃដើម (Initial State)
    const getInitialForm = () => ({
        type: 'meeting', 
        title: '', 
        date: new Date().toISOString().split('T')[0],
        start_time: getCurrentTime(), 
        end_time: getCurrentTime(1),
        participants: '', 
        location: '', 
        room: '', 
        link: '', 
        color: 'green'
    })

    const getCurrentTime = (addHours = 0) => {
        const now = new Date();
        now.setHours(now.getHours() + addHours);
        return now.toTimeString().slice(0, 5);
    }

    // ២. បង្កើត Form ចេញពី Initial State
    const form = reactive(getInitialForm())

    // មុខងារសម្រាប់សម្អាតទិន្នន័យក្នុង Form ឱ្យទៅជាដើមវិញ
    const resetForm = () => {
        Object.assign(form, getInitialForm())
    }

    const TABS = [
        { id: 'meeting', label: 'កិច្ចប្រជុំ', theme: '#e54d42', gradient: 'linear-gradient(135deg, #ff6b6b, #e54d42)', icon: 'bi bi-camera-video' },
        { id: 'appointment', label: 'ការណាត់', theme: '#4285f4', gradient: 'linear-gradient(135deg, #6ab0ff, #4285f4)', icon: 'bi bi-calendar-event' },
        { id: 'task', label: 'ការងារ', theme: '#34a853', gradient: 'linear-gradient(135deg, #51cf66, #34a853)', icon: 'bi bi-check2-circle' }
    ]

    const COLOR_OPTIONS = [
        { id: 'red', hex: '#ff6b6b', label: 'បន្ទាន់' },
        { id: 'yellow', hex: '#fcc419', label: 'មធ្យម' },
        { id: 'green', hex: '#51cf66', label: 'ធម្មតា' }
    ]

    watch(() => form.type, (val) => {
        if (val !== 'meeting') form.room = '';
        if (val === 'task') form.participants = '';
    });

    const activeTab = computed(() => TABS.find(t => t.id === form.type) || TABS[0])
    const activeTheme = computed(() => activeTab.value.theme)
    const activeGradient = computed(() => activeTab.value.gradient)

    const closeModal = () => {
        emit('update:modelValue', false)
    }

    const handleSave = async () => {
        if (form.start_time >= form.end_time) {
            return alertStore.show('ម៉ោងបញ្ចប់ត្រូវធំជាងម៉ោងចាប់ផ្តើម', 'error');
        }

        loading.value = true;
        try {
            const payload = { 
                type: form.type,
                title: form.title,
                date: form.date,
                start_time: form.start_time,
                end_time: form.end_time,
                location: form.location || null,
                link: form.link || null,
                color_id: form.color,
                participants: form.participants ? form.participants.split(',').map(p => p.trim()) : null,
                room: form.type === 'meeting' ? form.room : null
            };

            await api.post('/schedules', payload);
            alertStore.show('រក្សាទុកជោគជ័យ', 'success');
            
            // ៣. ចំណុចសំខាន់៖ សម្អាត Form ក្រោយ Save ជោគជ័យ
            resetForm(); 
            
            emit('refresh');
            closeModal();
        } catch (err) {
            const msg = err.response?.data?.message || 'បរាជ័យក្នុងការ Insert';
            alertStore.show(msg, 'error');
        } finally {
            loading.value = false;
        }
    }
</script>
 
<style scoped>
    /* Use @ to start from resources/js/ */
    @import "@/css/scheduler-form.css";
</style>