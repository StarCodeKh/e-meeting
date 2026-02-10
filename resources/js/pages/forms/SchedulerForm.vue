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
                            <div class="datetime-pill-container d-flex align-items-center flex-grow-1">
                                <DatePicker v-model="form.date" :popover="{ visibility: 'click' }" color="blue">
                                    <template #default="{ inputValue, inputEvents }">
                                        <input class="date-input-clean khmer-font" :value="inputValue" v-on="inputEvents" readonly placeholder="ជ្រើសរើសថ្ងៃ..." />
                                    </template>
                                </DatePicker>

                                <div class="vr mx-2 opacity-25" style="height: 20px;"></div>

                                <div class="time-picker-minimal d-flex align-items-center">
                                    <div class="time-box">
                                        <input v-model="form.start_time" type="time" class="time-field-inline" />
                                        <span class="ampm-text">{{ getAMPM(form.start_time) }}</span>
                                    </div>
                                    <span class="separator">-</span>
                                    <div class="time-box">
                                        <input v-model="form.end_time" type="time" class="time-field-inline" />
                                        <span class="ampm-text">{{ getAMPM(form.end_time) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row" v-if="form.type !== 'task'">
                            <i class="bi bi-people icon-gray"></i>
                            <div class="position-relative flex-grow-1">
                                <div class="pill-multiselect-header d-flex justify-content-between align-items-center" @click="showUserDropdown = !showUserDropdown">
                                    <span class="selected-text text-truncate khmer-font">
                                        {{ selectedUsers.length > 0 ? selectedUsers.map(u => u.name).join(', ') : 'ជ្រើសរើសអ្នកចូលរួម...' }}
                                    </span>
                                    <i class="bi bi-chevron-down small opacity-50"></i>
                                </div>

                                <div v-if="showUserDropdown" class="multiselect-dropdown-card shadow-lg border">
                                    <div class="search-container p-2 border-bottom">
                                        <div class="search-box-inner d-flex align-items-center px-2">
                                            <i class="bi bi-search opacity-50 me-2"></i>
                                            <input v-model="userSearch" type="text" placeholder="ស្វែងរកឈ្មោះ..." class="search-input-none flex-grow-1" />
                                        </div>
                                    </div>
                                    <div class="options-list" style="max-height: 200px; overflow-y: auto;">
                                        <div v-for="user in filteredUsers" :key="user.email" class="option-row d-flex align-items-center" @click="toggleUser(user)">
                                            <div class="checkbox-box me-3" :class="{ 'checked': isUserSelected(user) }">
                                                <i v-if="isUserSelected(user)" class="bi bi-check text-white"></i>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="option-name khmer-font" style="font-size: 14px;">{{ user.name }}</span>
                                                <span class="text-muted" style="font-size: 11px;">{{ user.email }}</span>
                                            </div>
                                        </div>
                                        <div v-if="filteredUsers.length === 0" class="p-3 text-center text-muted small khmer-font">មិនមានទិន្នន័យ</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <i class="bi bi-geo-alt icon-gray"></i>
                            <div class="pill-group w-100 d-flex gap-2">
                                <input v-model="form.location" type="text" placeholder="ទីតាំង" class="pill-input flex-grow-1" />
                                <input v-if="form.type === 'meeting'" v-model="form.room" type="text" placeholder="បន្ទប់" class="pill-input w-25" />
                            </div>
                        </div>

                        <div class="form-row align-items-start">
                            <i class="bi bi-card-text icon-gray mt-2"></i>
                            <textarea v-model="form.description" rows="2" placeholder="ពណ៌នាការងារលម្អិត..." class="pill-input khmer-font w-100 py-2"></textarea>
                        </div>

                        <div class="form-row">
                            <i class="bi bi-link-45deg icon-gray"></i>
                            <input v-model="form.link" type="url" placeholder="លីងតំណភ្ជាប់..." class="pill-input w-100" />
                        </div>

                        <div class="form-row align-items-center">
                            <i class="bi bi-file-earmark-pdf icon-gray"></i>
                            <div class="flex-grow-1">
                                <div class="file-upload-wrapper">
                                    <input type="file" id="fileInput" accept=".pdf" class="d-none" @change="handleFileChange" />
                                    <label for="fileInput" class="pill-input khmer-font d-flex align-items-center justify-content-between cursor-pointer w-100">
                                        <span class="text-truncate" :class="{ 'text-muted': !selectedFileName }">
                                            {{ selectedFileName || 'ភ្ជាប់ឯកសារពិភាក្សា (PDF)...' }}
                                        </span>
                                        <i v-if="!selectedFileName" class="bi bi-cloud-arrow-up fs-5"></i>
                                        <i v-else class="bi bi-x-circle text-danger fs-5" @click.prevent="removeFile"></i>
                                    </label>
                                </div>
                                <small v-if="fileError" class="text-danger khmer-font small mt-1 d-block">{{ fileError }}</small>
                            </div>
                        </div>

                        <div class="mt-3">
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

                    <div class="modal-footer-custom d-flex justify-content-between mt-5 pt-3 border-top">
                        <button type="button" class="btn-cancel khmer-font d-flex align-items-center" @click="closeModal">
                            <i class="bi bi-x-circle me-2"></i> បោះបង់
                        </button>
                        <button type="submit" class="btn-save-dynamic khmer-font d-flex align-items-center text-white border-0 shadow-sm" :disabled="loading" :style="{ background: activeGradient }">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-check2-circle me-2"></i> 
                            {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<script setup>
    import { ref, reactive, computed, onMounted } from 'vue'
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/dist/style.css';
    import api from '@/api/axios'
    import { alertStore } from '@/stores/alert'

    const props = defineProps({ modelValue: Boolean })
    const emit = defineEmits(['update:modelValue', 'refresh'])

    // --- State ---
    const loading = ref(false)
    const showUserDropdown = ref(false)
    const userSearch = ref('')
    const users = ref([]) 
    const selectedUsers = ref([]) 

    const getCurrentTime = (addHours = 0) => {
        const now = new Date();
        now.setHours(now.getHours() + addHours);
        return now.toTimeString().slice(0, 5);
    }

    const getInitialForm = () => ({
        type: 'meeting', 
        title: '', 
        description: '',
        date: new Date().toISOString().split('T')[0],
        start_time: getCurrentTime(), 
        end_time: getCurrentTime(1),
        participants: '', 
        location: '', 
        room: '', 
        link: '', 
        color_id: 'green'
    })

    const form = reactive(getInitialForm())

    // --- API Methods ---
    const fetchUsers = async () => {
        try {
            const res = await api.get('/users?per_page=100');
            users.value = res.data.data.map(u => ({
                name: u.name,
                email: u.email
            }));
        } catch (err) {
            console.error("Fetch users error:", err);
        }
    }

    onMounted(() => {
        fetchUsers();
    });

    // --- Helpers ---
    const getAMPM = (timeStr) => {
        if (!timeStr) return '--';
        const hour = parseInt(timeStr.split(':')[0]);
        return hour >= 12 ? 'PM' : 'AM';
    }

    const toggleUser = (user) => {
        const index = selectedUsers.value.findIndex(u => u.email === user.email);
        if (index === -1) { 
            selectedUsers.value.push(user); 
        } else { 
            selectedUsers.value.splice(index, 1); 
        }
        // Update form ឱ្យមានតែ Array នៃ Email
        form.participants = selectedUsers.value.map(u => u.email);
    }

    const isUserSelected = (user) => selectedUsers.value.some(u => u.email === user.email);

    const filteredUsers = computed(() => {
        const s = userSearch.value.toLowerCase();
        return users.value.filter(u => 
            u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s)
        );
    })

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

    const activeTab = computed(() => TABS.find(t => t.id === form.type) || TABS[0])
    const activeTheme = computed(() => activeTab.value.theme)
    const activeGradient = computed(() => activeTab.value.gradient)

    const closeModal = () => {
        showUserDropdown.value = false;
        emit('update:modelValue', false);
    }



    const selectedFileName = ref('')
    const fileError = ref('')
    const selectedFile = ref(null)

    // ១. មុខងារចាប់យក File និងឆែកប្រភេទ
    const handleFileChange = (event) => {
        const file = event.target.files[0];
        fileError.value = '';

        if (file) {
            if (file.type !== 'application/pdf') {
                fileError.value = 'សូមជ្រើសរើសតែឯកសារប្រភេទ PDF ប៉ុណ្ណោះ!';
                selectedFile.value = null;
                selectedFileName.value = '';
                return;
            }
            
            // បើលើសពី 5MB (Optional)
            if (file.size > 5 * 1024 * 1024) {
                fileError.value = 'ឯកសារមិនអាចធំជាង 5MB ឡើយ!';
                return;
            }

            selectedFile.value = file;
            selectedFileName.value = file.name;
        }
    }

    // ២. លុប File ចេញវិញ
    const removeFile = () => {
        selectedFile.value = null;
        selectedFileName.value = '';
        const input = document.getElementById('fileInput');
        if (input) input.value = '';
    }

    // ៣. កែសម្រួល handleSave ដើម្បីផ្ញើជា FormData
    const handleSave = async () => {
        loading.value = true;
        try {
            const formData = new FormData();
            
            // បំពេញទិន្នន័យចូលក្នុង FormData
            Object.keys(form).forEach(key => {
                if (key === 'date') {
                    const formattedDate = form.date instanceof Date ? form.date.toISOString().split('T')[0] : form.date;
                    formData.append(key, formattedDate);
                } else if (key === 'participants') {
                    // ប្រសិនបើជា Array ត្រូវបញ្ជូនតាមរយៈ loop
                    form.participants.forEach(email => formData.append('participants[]', email));
                } else {
                    formData.append(key, form[key] || '');
                }
            });

            // បន្ថែម File ប្រសិនបើមាន
            if (selectedFile.value) {
                formData.append('attachment', selectedFile.value);
            }

            // ផ្ញើទៅ API (Headers នឹងត្រូវបានកំណត់អូតូជា multipart/form-data ដោយ Axios)
            await api.post('/schedules', formData);

            alertStore.show('រក្សាទុកជោគជ័យ', 'success');
            emit('refresh');
            closeModal();
            
            // Reset State
            Object.assign(form, getInitialForm());
            selectedUsers.value = [];
            removeFile();

        } catch (err) {
            const msg = err.response?.data?.message || 'បរាជ័យក្នុងការរក្សាទុក';
            alertStore.show(msg, 'error');
        } finally {
            loading.value = false;
        }
    }
        
    // const handleSave = async () => {
    //     loading.value = true;
    //     try {
    //         const payload = {
    //             ...form,
    //             date: form.date instanceof Date ? form.date.toISOString().split('T')[0] : form.date,
    //         };

    //         await api.post('/schedules', payload);
    //         alertStore.show('រក្សាទុកជោគជ័យ', 'success');
    //         emit('refresh');
    //         closeModal();
    //         // Reset state
    //         Object.assign(form, getInitialForm());
    //         selectedUsers.value = [];
    //     } catch (err) {
    //         alertStore.show('បរាជ័យក្នុងការរក្សាទុក', 'error');
    //     } finally {
    //         loading.value = false;
    //     }
    // }
</script>

<style scoped>
    /* Use @ to start from resources/js/ */
    @import "@/css/scheduler-form.css";

    .datetime-pill-container:focus-within {
        background: #ffffff;
        border-color: v-bind(activeTheme);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .ampm-text {
        font-size: 10px;
        font-weight: 800;
        color: white;
        background: v-bind(activeTheme); 
        padding: 4px 10px;
        border-radius: 5px;
        text-transform: uppercase;
        min-width: 35px;
        text-align: center;
    }



    .cursor-pointer {
        cursor: pointer;
    }
    .file-upload-wrapper label:hover {
        background: #f8f9fa;
        border-color: #dee2e6;
    }
    .icon-gray {
        width: 24px;
        font-size: 1.2rem;
        color: #adb5bd;
    }
    .bi-file-earmark-pdf {
        color:v-bind(activeTheme); 
    }

</style>