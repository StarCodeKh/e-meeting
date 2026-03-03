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
                    <div class="mb-4">
                        <input v-model="form.title" type="text" class="form-control khmer-font fs-4 fw-bold border-0 border-bottom bg-transparent rounded-0 px-0 shadow-none pb-2 transition-all" 
                            :style="{ borderBottomColor: form.title ? activeTheme : '#eee' }" 
                            placeholder="បញ្ចូលចំណងជើង..." required>
                    </div>

                    <div class="modal-body p-0">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="bg-light p-2 rounded-3 d-flex flex-column flex-md-row align-items-center px-3 border gap-2">
                                    <div class="d-flex align-items-center w-100 w-md-auto">
                                        <i class="bi bi-calendar3 me-2 transition-all" :style="{ color: form.date ? activeTheme : '#6c757d' }"></i>
                                        <DatePicker v-model="form.date" :popover="{ visibility: 'click' }" color="blue">
                                            <template #default="{ inputValue, inputEvents }">
                                                <input class="form-control form-control-sm border-0 bg-transparent shadow-none khmer-font" 
                                                    :value="inputValue" v-on="inputEvents" readonly placeholder="ជ្រើសរើសថ្ងៃ..." />
                                            </template>
                                        </DatePicker>
                                    </div>

                                    <div class="vr mx-2 opacity-25 d-none d-md-block" style="height: 20px;"></div>
                                    
                                    <div class="d-flex align-items-center gap-1 justify-content-between flex-grow-1 w-100 w-md-auto">
                                        <div class="d-flex align-items-center gap-1">
                                            <input v-model="form.start_time" type="time" class="border-0 bg-transparent fw-bold p-0" style="width: 75px; font-size: 0.9rem;">
                                            <span class="badge rounded-3 px-2 py-1 transition-all" :style="{ background: activeTheme, fontSize: '0.7rem' }">{{ getAMPM(form.start_time) }}</span>
                                        </div>
                                        <span class="mx-1 text-muted small">-</span>
                                        <div class="d-flex align-items-center gap-1">
                                            <input v-model="form.end_time" type="time" class="border-0 bg-transparent fw-bold p-0" style="width: 75px; font-size: 0.9rem;">
                                            <span class="badge rounded-3 px-2 py-1 transition-all" :style="{ background: activeTheme, fontSize: '0.7rem' }">{{ getAMPM(form.end_time) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 position-relative" v-if="form.type !== 'task'">
                                <div class="input-group bg-light rounded-3 border pill-multiselect-header cursor-pointer transition-all" @click="showUserDropdown = !showUserDropdown">
                                    <span class="input-group-text border-0 bg-transparent">
                                        <i class="bi bi-people transition-all" :style="{ color: selectedUsers.length ? activeTheme : '#6c757d' }"></i>
                                    </span>
                                    <div class="form-control khmer-font border-0 bg-transparent shadow-none py-2 d-flex align-items-center overflow-hidden">
                                        <span class="text-truncate" :class="{ 'text-muted': !selectedUsers.length }">
                                            {{ selectedUsers.length > 0 ? selectedUsers.map(u => u.name).join(', ') : 'ជ្រើសរើសអ្នកចូលរួម...' }}
                                        </span>
                                    </div>
                                    <i class="bi px-3 transition-all" :class="showUserDropdown ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem; color: #6c757d"></i>
                                </div>

                                <div v-if="showUserDropdown" class="bg-white rounded-3 border mt-1 w-100 overflow-hidden shadow-sm position-absolute z-3">
                                    <div class="p-2 border-bottom bg-light">
                                        <input v-model="userSearch" type="text" class="form-control form-control-sm khmer-font shadow-none" placeholder="ស្វែងរកឈ្មោះ..." @click.stop>
                                    </div>

                                    <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center justify-content-between cursor-pointer" @click.stop="toggleSelectAll">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0 transition-all" 
                                                :style="{ width: '24px', height: '24px', background: isAllSelected ? activeTheme : '#eee', color: isAllSelected ? 'white' : '#666' }">
                                                <i v-if="isAllSelected" class="bi bi-check-all"></i>
                                                <i v-else class="bi bi-people-fill" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <span class="khmer-font small fw-bold" :class="{ 'text-primary': isAllSelected }" :style="isAllSelected ? { color: activeTheme } : {}">
                                                {{ isAllSelected ? 'ដកចេញទាំងអស់' : 'ជ្រើសរើសទាំងអស់' }}
                                            </span>
                                        </div>
                                        <span class="badge rounded-pill bg-secondary" style="font-size: 0.65rem;">{{ filteredUsers.length }} នាក់</span>
                                    </div>

                                    <div class="overflow-auto" style="max-height: 200px;">
                                        <div v-for="user in filteredUsers" :key="user.email" 
                                            class="d-flex align-items-center px-3 py-2 border-bottom-faint cursor-pointer hover-bg-light transition-all" 
                                            @click.stop="toggleUser(user)">
                                           <div class="rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0 overflow-hidden" 
                                                style="width: 30px; height: 30px; background: #eee;">
                                                
                                                <img v-if="user.image" 
                                                    :src="user.image" 
                                                    class="w-100 h-100 object-fit-cover"
                                                    @error="user.image = null"> <span v-else>{{ user.name?.charAt(0) }}</span>
                                            </div>
                                            <div class="flex-grow-1 khmer-font small fw-bold text-truncate" :style="isUserSelected(user) ? { color: activeTheme } : {}">{{ user.name }}</div>
                                            <i v-if="isUserSelected(user)" class="bi bi-check-lg" :style="{ color: activeTheme }"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div :class="form.type === 'meeting' ? 'col-md-6' : 'col-12'">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                    <i class="bi bi-geo-alt me-2 transition-all" :style="{ color: form.location ? activeTheme : '#6c757d' }"></i>
                                    <input v-model="form.location" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="ទីតាំង">
                                </div>
                            </div>

                            <div class="col-md-3" v-if="form.type === 'meeting'">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                    <i class="bi bi-layers me-2 transition-all" :style="{ color: form.floor ? activeTheme : '#6c757d' }"></i>
                                    <input v-model="form.floor" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="ជាន់">
                                </div>
                            </div>

                            <div class="col-md-3" v-if="form.type === 'meeting'">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                    <i class="bi bi-door-open me-2 transition-all" :style="{ color: form.room ? activeTheme : '#6c757d' }"></i>
                                    <input v-model="form.room" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="បន្ទប់">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-start">
                                    <i class="bi bi-card-text mt-2 me-2 transition-all" :style="{ color: form.description ? activeTheme : '#6c757d' }"></i>
                                    <textarea v-model="form.description" rows="2" class="form-control khmer-font border-0 bg-transparent shadow-none py-2" placeholder="ពណ៌នាការងារលម្អិត..."></textarea>
                                </div>
                            </div>

                            <template v-if="form.type === 'meeting'">
                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                        <i class="bi bi-link-45deg me-2 transition-all" :style="{ color: form.link ? activeTheme : '#6c757d' }"></i>
                                        <input v-model="form.link" type="url" class="form-control border-0 bg-transparent shadow-none small" placeholder="លីងតំណភ្ជាប់">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100 transition-all" :style="selectedFileName ? { borderColor: activeTheme, backgroundColor: '#fff' } : {}">
                                        <i class="bi bi-file-earmark-pdf me-2 transition-all" :style="{ color: selectedFileName ? activeTheme : '#6c757d' }"></i>
                                        <label class="form-control border-0 bg-transparent shadow-none mb-0 flex-grow-1 cursor-pointer khmer-font text-muted p-0 small text-truncate">
                                            <input type="file" class="d-none" @change="handleFileChange" accept=".pdf" id="fileInput">
                                            <span v-if="selectedFileName" :style="{ color: activeTheme }">{{ selectedFileName }}</span>
                                            <span v-else>ភ្ជាប់ PDF...</span>
                                        </label>
                                        <i v-if="selectedFileName" class="bi bi-x-circle text-danger ms-1 cursor-pointer" @click.stop="removeFile"></i>
                                    </div>
                                    <small v-if="fileError" class="text-danger khmer-font small mt-1 d-block">{{ fileError }}</small>
                                </div>
                            </template>

                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-white mt-2">
                                    <label class="khmer-font small fw-bold text-muted mb-2 d-block">កម្រិតអាទិភាព</label>
                                    <div class="d-flex justify-content-between gap-2">
                                        <div v-for="color in COLOR_OPTIONS" :key="color.id" 
                                            class="d-flex align-items-center cursor-pointer p-2 px-3 rounded-2 transition-all flex-grow-1 justify-content-center" 
                                            :style="form.color_id === color.id ? { background: color.hex + '15' } : {}"
                                            @click="form.color_id = color.id">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center transition-all" 
                                                :style="{ width: '18px', height: '18px', backgroundColor: color.hex }">
                                                <i v-if="form.color_id === color.id" class="bi bi-check text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <span class="khmer-font small transition-all" :style="form.color_id === color.id ? { color: color.hex, fontWeight: 'bold' } : { color: '#666' }">{{ color.label }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pt-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-link text-decoration-none text-muted khmer-font p-0" @click="closeModal">
                            <i class="bi bi-x-circle me-1"></i> បោះបង់
                        </button>

                        <button type="submit" class="btn khmer-font px-5 py-2 rounded-3 shadow-sm text-white border-0 transition-all" :disabled="loading" :style="{ background: activeGradient }">
                            <template v-if="!loading">
                                <i class="bi bi-check2-circle me-2"></i> រក្សាទុកទិន្នន័យ
                            </template>
                            <template v-else>
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <span>កំពុងរក្សាទុក...</span>
                            </template>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<script setup>
    import { ref, reactive, computed, onMounted, watch } from 'vue'
    import { getScheduleFormOptions } from '@/services/ScheduleTypes';
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/dist/style.css';
    import api from '@/api/axios'
    import { alertStore } from '@/stores/alert'

    const props = defineProps({ modelValue: Boolean })
    const emit = defineEmits(['update:modelValue', 'refresh'])

    const loading = ref(false)
    const showUserDropdown = ref(false)
    const userSearch = ref('')
    const users = ref([]) 
    const selectedUsers = ref([])
    const scheduleTypes = ref([])
    const priorities = ref([])
    const selectedFileName = ref('')
    const fileError = ref('')
    const selectedFile = ref(null)

    const getCurrentTime = (addHours = 0) => {
        const now = new Date();
        now.setHours(now.getHours() + addHours);
        return now.toTimeString().slice(0, 5);
    }

    const getInitialForm = () => ({
        type: '',
        title: '', 
        description: '',
        date: new Date(),
        start_time: getCurrentTime(), 
        end_time: getCurrentTime(1),
        participants: [],
        location: '', 
        floor: '',
        room: '', 
        link: '', 
        color_id: '' 
    })

    const form = reactive(getInitialForm())

    // --- Logic សម្រាប់ Check All ---
    const isAllSelected = computed(() => {
        if (filteredUsers.value.length === 0) return false;
        return filteredUsers.value.every(user => isUserSelected(user));
    });

    const toggleSelectAll = () => {
        if (isAllSelected.value) {
            const filteredEmails = filteredUsers.value.map(u => u.email);
            selectedUsers.value = selectedUsers.value.filter(u => !filteredEmails.includes(u.email));
        } else {
            filteredUsers.value.forEach(user => {
                if (!isUserSelected(user)) {
                    selectedUsers.value.push(user);
                }
            });
        }
        form.participants = selectedUsers.value.map(u => u.email);
    };

    // --- ចប់ Logic Check All ---
    watch(() => form.type, (newType) => {
        if (newType !== 'meeting') {
            form.link = '';
            form.room = '';
            removeFile();
        }
    })

    const fetchUsers = async () => {
        try {
            const res = await api.get('/participants?per_page=100');
            
            users.value = res.data.data.map(u => {
                let photo = u.avatar_url || u.image || null;
                
                if (photo) {
                    photo = encodeURI(photo); 
                }

                return { 
                    name: u.name,
                    email: u.email,
                    image: photo
                };
            });

        } catch (err) { 
            console.error(err);
        }
    }

    const TABS = computed(() => scheduleTypes.value.map(type => ({
        id: type.slug, label: type.name, theme: type.color_hex, gradient: type.color_gradient, icon: type.icon
    })));

    const COLOR_OPTIONS = computed(() => priorities.value.map(p => ({
        id: p.slug, hex: p.color_hex, label: p.name
    })));

    const activeTab = computed(() => TABS.value.find(t => t.id === form.type) || { theme: '#e54d42', gradient: '' });
    const activeTheme = computed(() => activeTab.value.theme)
    const activeGradient = computed(() => activeTab.value.gradient)

    const filteredUsers = computed(() => {
        const s = userSearch.value.toLowerCase().trim();
        return users.value.filter(u => u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s));
    })

    onMounted(async () => {
        fetchUsers(); 
        try {
            const response = await getScheduleFormOptions(); 
            scheduleTypes.value = response.types || [];
            priorities.value = response.priorities || [];
            if (scheduleTypes.value.length > 0) form.type = scheduleTypes.value[0].slug;
            if (priorities.value.length > 0) form.color_id = priorities.value[0].slug;
        } catch (err) { console.error(err) }
    });

    const getAMPM = (timeStr) => {
        if (!timeStr) return '--';
        const hour = parseInt(timeStr.split(':')[0]);
        return hour >= 12 ? 'PM' : 'AM';
    }

    const toggleUser = (user) => {
        const index = selectedUsers.value.findIndex(u => u.email === user.email);
        if (index === -1) selectedUsers.value.push(user); 
        else selectedUsers.value.splice(index, 1);
        form.participants = selectedUsers.value.map(u => u.email);
    }

    const isUserSelected = (user) => selectedUsers.value.some(u => u.email === user.email);

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        fileError.value = '';
        if (file) {
            if (file.type !== 'application/pdf') return fileError.value = 'សូមជ្រើសរើសតែ PDF!';
            if (file.size > 5 * 1024 * 1024) return fileError.value = 'ឯកសារធំជាង 5MB!';
            selectedFile.value = file;
            selectedFileName.value = file.name;
        }
    }

    const removeFile = () => {
        selectedFile.value = null;
        selectedFileName.value = '';
        const input = document.getElementById('fileInput');
        if (input) input.value = '';
    }

    const closeModal = () => {
        showUserDropdown.value = false;
        userSearch.value = ''; // សម្អាតការស្វែងរកពេលបិទ
        emit('update:modelValue', false);
    }

    const handleSave = async () => {
        loading.value = true;
        try {
            const formData = new FormData();
            
            // បញ្ជូនទិន្នន័យ form
            Object.keys(form).forEach(key => {
                if (key === 'date') {
                    // ការពារបញ្ហា Timezone ដោយប្រើ Local Date Format
                    const d = form.date;
                    const formattedDate = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                    formData.append(key, formattedDate);
                } else if (key === 'participants') {
                    // បញ្ជូន Participants ជា Array
                    if (form.participants.length > 0) {
                        form.participants.forEach(email => formData.append('participants[]', email));
                    } else {
                        formData.append('participants[]', '');
                    }
                } else {
                    formData.append(key, form[key] || '');
                }
            });

            if (selectedFile.value) formData.append('attachment', selectedFile.value);

            await api.post('/schedules', formData);
            alertStore.show('រក្សាទុកជោគជ័យ', 'success');
            
            emit('refresh');
            closeModal();
            
            // Reset ព័ត៌មានក្នុង Form
            Object.assign(form, getInitialForm());
            selectedUsers.value = [];
            removeFile();
            
        } catch (err) {
            alertStore.show(err.response?.data?.message || 'បរាជ័យ', 'error');
        } finally { loading.value = false; }
    }
</script>

<style scoped>
    /* Use @ to start from resources/js/ */
    @import "@/css/scheduler-form.css";
</style>