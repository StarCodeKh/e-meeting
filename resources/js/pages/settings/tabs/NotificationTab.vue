<template>
    <div class="space-y-4">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="khmer-font text-muted mt-2">កំពុងទាញយកការកំណត់...</p>
        </div>

        <div v-else class="fade-in">
            <div class="d-flex align-items-center justify-content-between p-3 border rounded-4 mb-3 bg-light shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                        <i class="bi bi-telegram fs-4"></i>
                    </div>
                    <div>
                        <h6 class="khmer-font fw-bold mb-1 text-dark">ការជូនដំណឹងតាម Telegram</h6>
                        <p class="text-muted small mb-0 khmer-font">ផ្ញើសាររំលឹកទៅកាន់ Telegram Bot</p>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input fs-3" type="checkbox" v-model="settings.telegram_enabled" @change="saveSettings">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between p-3 border rounded-4 mb-4 bg-light shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 p-2 rounded-3 text-info">
                        <i class="bi bi-bell-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="khmer-font fw-bold mb-1 text-dark">ការជូនដំណឹងលើទូរស័ព្ទ (Push)</h6>
                        <p class="text-muted small mb-0 khmer-font">ផ្ញើ Notification ទៅកាន់ Mobile App</p>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input fs-3" type="checkbox" v-model="settings.push_enabled" @change="saveSettings">
                </div>
            </div>

            <div :class="['card border-0 bg-white p-4 rounded-4 shadow-sm', { 'opacity-50 pointer-events-none': !settings.telegram_enabled && !settings.push_enabled }]">
                <label class="khmer-font mb-3 d-block text-dark fw-bold">
                    <i class="bi bi-clock-history me-2 text-primary"></i>ម៉ោងរំលឹកមុនកិច្ចប្រជុំ
                </label>
                <div class="row g-3">
                    <div class="col-md-8">
                        <select class="form-select form-select-lg khmer-font border-0 bg-light rounded-3" v-model="settings.reminder_time">
                            <option :value="5">៥ នាទីមុន</option>
                            <option :value="15">១៥ នាទីមុន</option>
                            <option :value="30">៣០ នាទីមុន</option>
                            <option :value="60">១ ម៉ោងមុន</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button @click="saveSettings" class="btn btn-primary btn-lg w-100 khmer-font shadow-sm d-flex align-items-center justify-content-center gap-2" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm" role="status"></span>
                            <i v-else class="bi bi-check-circle-fill fs-5"></i>
                            <span>{{ saving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកំណត់' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import axios from 'axios'
    import Swal from 'sweetalert2'

    const loading = ref(true)
    const saving = ref(false)
    
    const settings = ref({ 
        telegram_enabled: true, 
        push_enabled: true,
        reminder_time: 15 
    })

    const getAuthConfig = () => {
        const token = localStorage.getItem('access_token') || localStorage.getItem('token')
        return {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        }
    }

    const fetchSettings = async () => {
        try {
            loading.value = true
            const res = await axios.get('/api/notification-settings', getAuthConfig())
            
            const apiData = res.data.data 
            settings.value = {
                telegram_enabled: apiData.telegram.enabled,
                push_enabled: apiData.push_notification.enabled,
                reminder_time: apiData.telegram.reminder_time
            }
        } catch (error) {
            console.error("Fetch Error:", error)
        } finally {
            loading.value = false
        }
    }

    const saveSettings = async () => {
        saving.value = true
        try {

            await axios.post('/api/notification-settings', settings.value, getAuthConfig())
            
            Swal.fire({
                icon: 'success',
                title: 'ជោគជ័យ',
                text: 'ការកំណត់ត្រូវបានរក្សាទុក',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: { popup: 'khmer-font rounded-4' }
            })
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'បរាជ័យ',
                text: e.response?.data?.message || 'មិនអាចរក្សាទុកបានទេ',
                customClass: { popup: 'khmer-font rounded-4', confirmButton: 'btn btn-danger khmer-font' }
            })
        } finally {
            saving.value = false
        }
    }

    onMounted(fetchSettings)
</script>

<style scoped>
    @import url('@/assets/css/style.css');
</style>