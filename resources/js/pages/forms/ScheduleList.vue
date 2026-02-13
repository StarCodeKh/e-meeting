<template>
    <div class="container-fluid py-4 bg-light min-vh-100">
        <div class="card border-0 shadow-sm rounded-3 mb-4 p-4 card-header-custom border-top border-primary border-5">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <h4 class="khmer-font fw-bold mb-1 text-primary">បញ្ជីកាលវិភាគប្រជុំ</h4>
                    <p class="text-muted small mb-0 khmer-font">
                        លទ្ធផលសរុប៖ <span class="badge bg-primary-subtle text-primary rounded-3 px-3">{{ toKhmerNum(pagination.total || 0) }} កិច្ចប្រជុំ</span>
                    </p>
                </div>
                <div class="col-md-5">
                    <div class="search-box position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input v-model="searchQuery" type="text" class="form-control khmer-font py-2 ps-5 border-0 shadow-none bg-light rounded-3" 
                               placeholder="ស្វែងរកតាមចំណងជើង..." @input="handleSearch">
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <button class="btn btn-primary khmer-font rounded-3 shadow-sm px-4 py-2 w-100 w-md-auto" @click="goToCreate">
                        <i class="bi bi-plus-circle me-2"></i> បង្កើតថ្មី
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isLoading" class="row g-3">
            <div v-for="n in 6" :key="n" class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 skeleton-card animate-pulse">
                    <div class="skeleton-line mb-3" style="width: 30%;"></div>
                    <div class="skeleton-line mb-2" style="width: 80%;"></div>
                    <div class="skeleton-line mb-4" style="width: 60%;"></div>
                </div>
            </div>
        </div>

        <div v-else class="row g-3">
            <div v-for="item in meetings" :key="item.id" class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 meeting-card border-start border-4" :class="getBorderClass(item.color_id)">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge rounded-3 px-3 py-2 khmer-font shadow-sm" :class="getBadgeClass(item.color_id)">
                                    <i class="bi bi-door-open me-1"></i> {{ item.room }}
                                </span>
                                <span class="small text-muted khmer-font fw-bold border-start ps-2">
                                    <i class="bi bi-calendar-event me-1 text-primary"></i> {{ toKhmerNum(item.date) }}
                                </span>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle action-btn" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 khmer-font p-2 mt-2">
                                    <li><button class="dropdown-item py-2 rounded-2 text-primary" @click="openEditModal(item)">
                                        <i class="bi bi-pencil-square me-2"></i> កែសម្រួល</button></li>
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                    <li><button class="dropdown-item py-2 rounded-2 text-danger" @click="confirmDelete(item.id)">
                                        <i class="bi bi-trash3 me-2"></i> លុបចោល</button></li>
                                </ul>
                            </div>
                        </div>

                        <h5 class="khmer-font fw-bold mb-3 text-dark line-clamp-2 title-link" @click="openEditModal(item)">
                            {{ item.title }}
                        </h5>

                        <div class="mb-4 mt-auto">
                            <div class="small text-muted khmer-font mb-2">
                                <i class="bi bi-clock-fill text-primary me-1 opacity-75"></i> 
                                {{ toKhmerNum(item.start_time) }} - {{ toKhmerNum(item.end_time) }}
                            </div>
                            <div class="small text-muted khmer-font">
                                <i class="bi bi-geo-alt-fill text-danger me-1 opacity-75"></i> {{ item.location }}
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <a :href="item.link" target="_blank" class="btn btn-light border flex-grow-1 khmer-font py-2 btn-action shadow-sm small">
                                <i class="bi bi-camera-video me-1 text-primary"></i> ចូលរួម
                            </a>
                            <a v-if="item.attachment" :href="item.attachment" target="_blank" class="btn btn-outline-danger border flex-grow-1 khmer-font py-2 btn-action shadow-sm small">
                                <i class="bi bi-file-earmark-pdf me-1"></i> ឯកសារ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editMeetingModal" ref="modalElement" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
                <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden" :style="{ borderTop: `6px solid ${activeTheme}` }">
                    
                    <div class="d-flex bg-white border-bottom p-2 gap-2 justify-content-center">
                        <button v-for="tab in TABS" :key="tab.id" class="btn border-0 rounded-3 px-4 py-2 khmer-font transition-all d-flex align-items-center justify-content-center flex-grow-1" :style="editingItem.type === tab.id ? { background: tab.theme, color: 'white' } : { color: '#666', background: 'transparent' }" @click="editingItem.type = tab.id">
                            <i :class="tab.icon" class="me-2" :style="{ color: editingItem.type === tab.id ? 'white' : tab.theme }"></i> 
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="modal-body p-4 pt-4">
                        <div class="mb-4">
                            <input v-model="editingItem.title" type="text" class="form-control khmer-font fs-4 fw-bold border-0 border-bottom bg-transparent rounded-0 px-0 shadow-none pb-2 transition-all" :style="{ borderBottomColor: editingItem.title ? activeTheme : '#eee' }" placeholder="បញ្ចូលចំណងជើង...">
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="bg-light p-2 rounded-3 d-flex flex-column flex-md-row align-items-center px-3 border gap-2">
                                    <div class="d-flex align-items-center w-100 w-md-auto">
                                        <i class="bi bi-calendar3 me-2 transition-all" :style="{ color: editingItem.date ? activeTheme : '#6c757d' }"></i>
                                        <DatePicker v-model="editingItem.date" :popover="{ visibility: 'click' }" color="blue">
                                            <template #default="{ inputValue, inputEvents }">
                                                <input class="form-control form-control-sm border-0 bg-transparent shadow-none khmer-font p-0" :value="inputValue" v-on="inputEvents" readonly placeholder="ជ្រើសរើសថ្ងៃ..." />
                                            </template>
                                        </DatePicker>
                                    </div>

                                    <div class="vr mx-2 opacity-25 d-none d-md-block" style="height: 20px;"></div>
                                    
                                    <div class="d-flex align-items-center gap-1 justify-content-between flex-grow-1 w-100 w-md-auto">
                                        <div class="d-flex align-items-center gap-1">
                                            <input v-model="editingItem.start_time" type="time" class="border-0 bg-transparent fw-bold p-0" style="width: 75px; font-size: 0.9rem;">
                                            <span class="badge rounded-3 px-2 py-1 transition-all" :style="{ background: activeTheme, fontSize: '0.7rem' }">{{ getAMPM(editingItem.start_time) }}</span>
                                        </div>
                                        <span class="mx-1 text-muted small">-</span>
                                        <div class="d-flex align-items-center gap-1">
                                            <input v-model="editingItem.end_time" type="time" class="border-0 bg-transparent fw-bold p-0" style="width: 75px; font-size: 0.9rem;">
                                            <span class="badge rounded-3 px-2 py-1 transition-all" :style="{ background: activeTheme, fontSize: '0.7rem' }">{{ getAMPM(editingItem.end_time) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 position-relative">
                                <div class="input-group bg-light rounded-3 border pill-multiselect-header cursor-pointer transition-all" @click="toggleDropdown">
                                    <span class="input-group-text border-0 bg-transparent">
                                        <i class="bi bi-people transition-all" :style="{ color: editingItem.participants?.length ? activeTheme : '#6c757d' }"></i>
                                    </span>
                                    <div class="form-control khmer-font border-0 bg-transparent shadow-none py-2 d-flex align-items-center overflow-hidden">
                                        <span class="text-truncate" :class="{ 'text-muted': !editingItem.participants?.length }">
                                            {{ participantDisplayNames }}
                                        </span>
                                    </div>
                                    <i class="bi px-3 transition-all" :class="showUserDropdown ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem; color: #6c757d"></i>
                                </div>
                                
                                <div v-if="showUserDropdown" class="bg-white rounded-3 border mt-1 w-100 overflow-hidden shadow-sm position-absolute z-3">
                                    <div class="p-2 border-bottom bg-light">
                                        <input v-model="userSearchQuery" type="text" class="form-control form-control-sm khmer-font shadow-none" placeholder="ស្វែងរកឈ្មោះ..." @click.stop>
                                    </div>
                                    <div class="overflow-auto" style="max-height: 200px;">
                                        <div v-for="user in filteredUsers" :key="user.id" class="d-flex align-items-center px-3 py-2 border-bottom-faint cursor-pointer hover-bg-light transition-all" @click.stop="toggleUserSelection(user)">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0 transition-all" 
                                                :style="{ width: '30px', height: '30px', background: isUserSelected(user) ? activeTheme : '#eee', color: isUserSelected(user) ? 'white' : '#666' }">
                                                {{ user.name?.charAt(0) }}
                                            </div>
                                            <div class="flex-grow-1 khmer-font small fw-bold text-truncate" :style="isUserSelected(user) ? { color: activeTheme } : {}">{{ user.name }}</div>
                                            <i v-if="isUserSelected(user)" class="bi bi-check-lg" :style="{ color: activeTheme }"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                    <i class="bi bi-geo-alt me-2 transition-all" :style="{ color: editingItem.location ? activeTheme : '#6c757d' }"></i>
                                    <input v-model="editingItem.location" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="ទីតាំង">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                    <i class="bi bi-door-open me-2 transition-all" :style="{ color: editingItem.room ? activeTheme : '#6c757d' }"></i>
                                    <input v-model="editingItem.room" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="បន្ទប់">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-start">
                                    <i class="bi bi-card-text mt-2 me-2 transition-all" :style="{ color: editingItem.description ? activeTheme : '#6c757d' }"></i>
                                    <textarea v-model="editingItem.description" rows="2" class="form-control khmer-font border-0 bg-transparent shadow-none py-2" placeholder="ពណ៌នាការងារលម្អិត..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                    <i class="bi bi-link-45deg me-2 transition-all" :style="{ color: editingItem.link ? activeTheme : '#6c757d' }"></i>
                                    <input v-model="editingItem.link" type="url" class="form-control border-0 bg-transparent shadow-none small" placeholder="លីងតំណភ្ជាប់">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100 transition-all" :style="selectedFile ? { borderColor: activeTheme, backgroundColor: '#fff' } : {}">
                                    <i class="bi bi-file-earmark-pdf me-2 transition-all" :style="{ color: selectedFile || editingItem.attachment ? activeTheme : '#6c757d' }"></i>
                                    <label class="form-control border-0 bg-transparent shadow-none mb-0 flex-grow-1 cursor-pointer khmer-font text-muted p-0 small text-truncate">
                                        <span v-if="selectedFile" :style="{ color: activeTheme }">{{ selectedFile.name }}</span>
                                        <span v-else-if="editingItem.attachment" class="text-dark">{{ editingItem.attachment.split('/').pop() }}</span>
                                        <span v-else>ភ្ជាប់ PDF...</span>
                                        <input type="file" class="d-none" @change="handleFileChange" accept=".pdf">
                                    </label>
                                    <i v-if="selectedFile" class="bi bi-x-circle text-danger ms-1 cursor-pointer" @click.stop="selectedFile = null"></i>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="p-3 border rounded-3 bg-white">
                                    <label class="khmer-font small fw-bold text-muted mb-2 d-block">កម្រិតអាទិភាព</label>
                                    <div class="d-flex justify-content-between gap-2">
                                        <div v-for="color in COLOR_OPTIONS" :key="color.id" 
                                            class="d-flex align-items-center cursor-pointer p-2 px-3 rounded-2 transition-all flex-grow-1 justify-content-center" 
                                            :style="editingItem.color_id === color.id ? { background: color.hex + '15' } : {}"
                                            @click="editingItem.color_id = color.id">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center transition-all" 
                                                :style="{ width: '18px', height: '18px', backgroundColor: color.hex }">
                                                <i v-if="editingItem.color_id === color.id" class="bi bi-check text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <span class="khmer-font small transition-all" :style="editingItem.color_id === color.id ? { color: color.hex, fontWeight: 'bold' } : { color: '#666' }">{{ color.label }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 d-flex justify-content-between align-items-center border-top bg-white">
                        <button type="button" class="btn btn-link text-decoration-none text-muted khmer-font p-0" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> បោះបង់
                        </button>
                        <button class="btn khmer-font px-5 py-2 rounded-3 shadow-sm text-white border-0 transition-all" 
                                :disabled="isSaving" @click="updateMeeting" :style="{ background: activeGradient }">
                            <i v-if="!isSaving" class="bi bi-check2-circle me-2"></i>
                            <span v-else class="spinner-border spinner-border-sm me-2"></span>
                            {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <nav v-if="pagination.last_page > 1" class="mt-5 d-flex justify-content-center">
            <ul class="pagination shadow-sm rounded-3 overflow-hidden border-0 bg-white">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link py-2 px-3 border-0" @click="changePage(currentPage - 1)"><i class="bi bi-chevron-left"></i></button>
                </li>
                <li v-for="page in pagination.links?.slice(1, -1)" :key="page.label" 
                    class="page-item" :class="{ active: page.active }">
                    <button class="page-link py-2 px-3 border-0 khmer-font" @click="changePage(parseInt(page.label))">
                        {{ toKhmerNum(page.label) }}
                    </button>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === pagination.last_page }">
                    <button class="page-link py-2 px-3 border-0" @click="changePage(currentPage + 1)"><i class="bi bi-chevron-right"></i></button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue'
    import { ScheduleService } from '@/services/ScheduleService'
    import Swal from 'sweetalert2'
    import { Modal } from 'bootstrap'
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/dist/style.css';
    import api from '@/api/axios'

    // --- State Management ---
    const meetings = ref([])
    const pagination = ref({})
    const currentPage = ref(1)
    const isLoading = ref(false)
    const searchQuery = ref('')
    const editingItem = ref({})
    const isSaving = ref(false)
    const selectedFile = ref(null)
    const modalElement = ref(null)
    let modalInstance = null 

    const showUserDropdown = ref(false)
    const userSearchQuery = ref('')
    const allUsers = ref([])
    const isFetchingUsers = ref(false)

    // --- Constants ---
    const TABS = [
        { id: 'meeting', label: 'កិច្ចប្រជុំ', icon: 'bi bi-camera-video', theme: '#e54d42', gradient: 'linear-gradient(135deg, #ff6b6b, #e54d42)' },
        { id: 'appointment', label: 'ការណាត់', icon: 'bi bi-calendar-event', theme: '#fcc419', gradient: 'linear-gradient(135deg, #ffd43b, #fcc419)' },
        { id: 'task', label: 'ការងារ', icon: 'bi bi-check2-circle', theme: '#34a853', gradient: 'linear-gradient(135deg, #51cf66, #34a853)' }
    ]

    const COLOR_OPTIONS = [
        { id: 'red', hex: '#ff6b6b', label: 'បន្ទាន់' },
        { id: 'yellow', hex: '#fcc419', label: 'មធ្យម' },
        { id: 'green', hex: '#51cf66', label: 'ធម្មតា' }
    ]

    // --- Computed ---
    const activeTab = computed(() => TABS.find(t => t.id === editingItem.value.type) || TABS[0])
    const activeTheme = computed(() => activeTab.value.theme)
    const activeGradient = computed(() => activeTab.value.gradient)

    const filteredUsers = computed(() => {
        if (!allUsers.value) return []
        const query = userSearchQuery.value.toLowerCase().trim()
        return allUsers.value.filter(u => u.name?.toLowerCase().includes(query))
    })

    // --- Methods ---
    const fetchApiUsers = async () => {
        isFetchingUsers.value = true
        try {
            const res = await api.get('/users?per_page=100')
            allUsers.value = res.data?.data || []
        } catch (error) {
            console.error("Fetch Error:", error)
        } finally {
            isFetchingUsers.value = false
        }
    }

    const participantDisplayNames = computed(() => {
        if (!editingItem.value.participants?.length) return 'ជ្រើសរើសអ្នកចូលរួម...';
        
        return editingItem.value.participants.map(email => {
            const found = allUsers.value.find(u => u.email === email);
            return found ? found.name : email;
        }).join(', ');
    });

    const toggleDropdown = () => {
        showUserDropdown.value = !showUserDropdown.value
        if (showUserDropdown.value && allUsers.value.length === 0) fetchApiUsers()
    }

    const toggleUserSelection = (user) => {
        if (!editingItem.value.participants) editingItem.value.participants = [];
        
        const index = editingItem.value.participants.indexOf(user.email);
        
        if (index > -1) {
            editingItem.value.participants.splice(index, 1);
        } else {
            editingItem.value.participants.push(user.email);
        }
    };

    const isUserSelected = (user) => {
        return editingItem.value.participants?.includes(user.email)
    }

    const openEditModal = (item) => {
        editingItem.value = JSON.parse(JSON.stringify(item));
        selectedFile.value = null;

        if (editingItem.value.date) {
            const [year, month, day] = editingItem.value.date.split('-').map(Number);
            editingItem.value.date = new Date(year, month - 1, day);
        }

        if (item.participant_emails && Array.isArray(item.participant_emails)) {
            editingItem.value.participants = [...item.participant_emails];
        } else {
            editingItem.value.participants = [];
        }
        
        showUserDropdown.value = false;
        if (!modalInstance && modalElement.value) {
            modalInstance = new Modal(modalElement.value);
        }
        modalInstance?.show();
    };

    const updateMeeting = async () => {
        isSaving.value = true
        try {
            const data = new FormData()
            data.append('_method', 'PUT')

            Object.keys(editingItem.value).forEach(key => {
                let value = editingItem.value[key]
                
                if (key === 'date' && value instanceof Date) {
                    const year = value.getFullYear();
                    const month = String(value.getMonth() + 1).padStart(2, '0');
                    const day = String(value.getDate()).padStart(2, '0');
                    value = `${year}-${month}-${day}`;
                }

                if (!['attachment', 'participants', 'participant_emails'].includes(key) && value !== null) {
                    data.append(key, value)
                }
            })

            if (editingItem.value.participants && editingItem.value.participants.length > 0) {
                editingItem.value.participants.forEach((email) => {
                    data.append('participant_emails[]', email)
                })
            } else {
                data.append('participant_emails[]', '')
            }

            if (selectedFile.value) {
                data.append('attachment', selectedFile.value)
            }

            await ScheduleService.update(editingItem.value.id, data)
            
            modalInstance?.hide()
            await fetchMeetings(currentPage.value)
            
            Swal.fire({ 
                icon: 'success', 
                title: 'រក្សាទុកជោគជ័យ', 
                timer: 1500, 
                showConfirmButton: false,
                customClass: { popup: 'khmer-font' }
            })
        } catch (error) {
            // Error handling...
        } finally {
            isSaving.value = false
        }
    }

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        
        if (!file) {
            return;
        }

        if (file.type !== 'application/pdf') {
            Swal.fire({ 
                icon: 'error', 
                title: 'ប្រភេទឯកសារមិនត្រឹមត្រូវ', 
                text: 'សូមជ្រើសរើសឯកសារ PDF ប៉ុណ្ណោះ',
                customClass: { popup: 'khmer-font' }
            });
            event.target.value = '';
            return;
        }

        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'ឯកសារធំពេក', 
                text: 'ឯកសារមិនអាចលើសពី 5MB ឡើយ',
                customClass: { popup: 'khmer-font' }
            });
            event.target.value = '';
            return;
        }
        selectedFile.value = file;
    };
    
    const fetchMeetings = async (page = 1) => {
        isLoading.value = true
        try {
            const data = await ScheduleService.getAll(page, searchQuery.value)
            meetings.value = data.data
            pagination.value = data.meta
            currentPage.value = data.meta.current_page
        } catch (e) { console.error(e) } finally { isLoading.value = false }
    }

    const toKhmerNum = (n) => n?.toString().replace(/\d/g, d => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d])
    const getAMPM = (t) => t && parseInt(t.split(':')[0]) >= 12 ? 'PM' : 'AM'
    const getBadgeClass = (c) => ({ 'bg-danger-subtle text-danger': c === 'red', 'bg-warning-subtle text-warning': c === 'yellow', 'bg-success-subtle text-success': c === 'green' })
    const getBorderClass = (c) => ({ 'border-danger': c === 'red', 'border-warning': c === 'yellow', 'border-success': c === 'green' })

    const confirmDelete = async (id) => {
        if (!id) return

        const result = await Swal.fire({
        title: 'តើអ្នកប្រាកដទេ?',
            text: "ទិន្នន័យនេះនឹងត្រូវលុបចេញពីប្រព័ន្ធ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-2"></i> យល់ព្រមលុប',
            cancelButtonText: '<i class="bi bi-x-circle me-2"></i> បោះបង់',
            reverseButtons: true,
            customClass: { 
                popup: 'khmer-font shadow-lg rounded-4', 
                confirmButton: 'khmer-font rounded-3 px-4 py-2', 
                cancelButton: 'khmer-font rounded-3 px-4 py-2' 
            }
        })

        if (result.isConfirmed) {
            try {
                await ScheduleService.delete(id)
                Swal.fire({ title: 'ជោគជ័យ!', icon: 'success', timer: 2000, showConfirmButton: false })
                fetchMeetings(currentPage.value)
            } catch (error) {
                Swal.fire({ title: 'កំហុស!', text: 'មិនអាចលុបទិន្នន័យបានទេ!', icon: 'error' })
            }
        }
    }
    
    onMounted(async () => {
        await fetchMeetings()
        fetchApiUsers() 
        window.addEventListener('click', (e) => {
            const isHeaderClick = e.target.closest('.pill-multiselect-header')
            const isListClick = e.target.closest('.bg-white.rounded-3.border.mt-1')
            
            if (!isHeaderClick && !isListClick) {
                showUserDropdown.value = false
            }
        })
    })

</script>

<style scoped>
    @import "@/css/schedule-list.css";
</style>

