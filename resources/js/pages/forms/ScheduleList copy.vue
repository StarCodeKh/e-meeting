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
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden" :style="{ borderTop: `6px solid ${activeTheme}` }">
                    
                    <div class="d-flex bg-white border-bottom p-2 gap-2 justify-content-center">
                        <button v-for="tab in TABS" :key="tab.id" 
                            class="btn border-0 rounded-3 px-4 py-2 khmer-font transition-all d-flex align-items-center justify-content-center flex-grow-1" 
                            :style="editingItem.type === tab.id ? { background: tab.theme, color: 'white' } : { color: '#666', background: 'transparent' }" 
                            @click="editingItem.type = tab.id">
                            <i :class="tab.icon" class="me-2"></i> {{ tab.label }}
                        </button>
                    </div>

                    <div class="modal-body p-4 pt-5">
                        <div class="mb-4">
                            <input v-model="editingItem.title" type="text" 
                                class="form-control khmer-font fs-4 fw-bold border-0 border-bottom bg-transparent rounded-0 px-0 shadow-none pb-2" 
                                :style="{ borderBottomColor: activeTheme + ' !important' }"
                                placeholder="បញ្ចូលចំណងជើង...">
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                            <div class="bg-light p-2 rounded-3 rounded-md-3 d-flex flex-column flex-md-row align-items-center px-3 border gap-2">
                                
                                <div class="d-flex align-items-center w-100 w-md-auto">
                                    <i class="bi bi-calendar3 text-muted me-2"></i>
                                    <input v-model="editingItem.date" type="date" 
                                        class="form-control form-control-sm border-0 bg-transparent shadow-none khmer-font flex-grow-1" 
                                        style="min-width: 130px;">
                                </div>

                                <div class="vr mx-2 opacity-25 d-none d-md-block" style="height: 20px;"></div>
                                    <hr class="w-100 my-1 d-md-none opacity-10">
                                    <div class="d-flex align-items-center gap-1 justify-content-between justify-content-md-end flex-grow-1 w-100 w-md-auto">
                                        <div class="d-flex align-items-center gap-1">
                                            <input v-model="editingItem.start_time" type="time" 
                                                class="border-0 bg-transparent fw-bold p-0" style="width: 75px; font-size: 0.9rem;">
                                            <span class="badge rounded-3 px-2 py-1" :style="{ background: activeTheme, fontSize: '0.7rem' }">
                                                {{ getAMPM(editingItem.start_time) }}
                                            </span>
                                        </div>
                                        
                                        <span class="mx-1 text-muted small">-</span>
                                        
                                        <div class="d-flex align-items-center gap-1">
                                            <input v-model="editingItem.end_time" type="time" 
                                                class="border-0 bg-transparent fw-bold p-0" style="width: 75px; font-size: 0.9rem;">
                                            <span class="badge rounded-3 px-2 py-1" :style="{ background: activeTheme, fontSize: '0.7rem' }">
                                                {{ getAMPM(editingItem.end_time) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12"> 
                                <label class="form-label khmer-font small fw-bold text-muted opacity-75">អ្នកចូលរួម</label>
    
                                <div class="input-group bg-light rounded-3 border pill-multiselect-header cursor-pointer" 
                                    @click="toggleDropdown">
                                    <span class="input-group-text border-0 bg-transparent text-muted"><i class="bi bi-people"></i></span>
                                    <div class="form-control khmer-font border-0 bg-transparent shadow-none py-2 d-flex align-items-center overflow-hidden">
                                        <span class="text-truncate" :class="{ 'text-muted': !editingItem.participants?.length }">
                                            {{ 
                                                editingItem.participants?.length 
                                                ? editingItem.participants.map(u => typeof u === 'string' ? u : u.name).join(', ') 
                                                : 'ជ្រើសរើសអ្នកចូលរួម...' 
                                            }}
                                        </span>
                                    </div>
                                    <span class="input-group-text border-0 bg-transparent text-muted">
                                        <i class="bi" :class="showUserDropdown ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    </span>
                                </div>

                                <div v-if="showUserDropdown" class="bg-white rounded-3 border mt-1 w-100 overflow-hidden shadow-sm">
                                    <div class="p-2 border-bottom bg-light">
                                        <input v-model="userSearchQuery" type="text" 
                                            class="form-control form-control-sm khmer-font shadow-none" 
                                            placeholder="ស្វែងរកឈ្មោះ..." @click.stop>
                                    </div>

                                    <div class="overflow-auto" style="max-height: 200px;">
                                        <div v-if="isFetchingUsers" class="p-4 text-center">
                                            <div class="spinner-border spinner-border-sm text-muted"></div>
                                        </div>
                                        
                                        <template v-else>
                                            <div v-for="user in filteredUsers" :key="user.id" 
                                                class="d-flex align-items-center px-3 py-2 border-bottom-faint cursor-pointer hover-bg-light"
                                                @click.stop="toggleUserSelection(user)">
                                                
                                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" 
                                                    :style="{ width: '30px', height: '30px', fontSize: '0.75rem', 
                                                            background: isUserSelected(user) ? activeTheme : '#eee', 
                                                            color: isUserSelected(user) ? 'white' : '#666' }">
                                                    {{ user.name?.charAt(0) }}
                                                </div>
                                                
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="khmer-font small fw-bold text-truncate" :style="isUserSelected(user) ? { color: activeTheme } : {}">
                                                        {{ user.name }}
                                                    </div>
                                                </div>

                                                <i v-if="isUserSelected(user)" class="bi bi-check-lg" :style="{ color: activeTheme }"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2 bg-light rounded-3 border p-1 px-3 align-items-center">
                                    <i class="bi bi-geo-alt text-muted"></i>
                                    <input v-model="editingItem.location" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="ទីតាំង">
                                    <div class="vr opacity-25" style="height: 20px;"></div>
                                    <input v-model="editingItem.room" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font w-25" placeholder="បន្ទប់">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-start">
                                    <i class="bi bi-card-text text-muted mt-2 me-2"></i>
                                    <textarea v-model="editingItem.description" rows="2" class="form-control khmer-font border-0 bg-transparent shadow-none py-2" placeholder="ពណ៌នាការងារលម្អិត..."></textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center mb-2">
                                    <i class="bi bi-link-45deg text-muted me-2"></i>
                                    <input v-model="editingItem.link" type="url" class="form-control border-0 bg-transparent shadow-none" placeholder="លីងតំណភ្ជាប់...">
                                </div>
                                <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf text-muted me-2"></i>
                                    <label class="form-control border-0 bg-transparent shadow-none mb-0 flex-grow-1 cursor-pointer khmer-font text-muted">
                                        {{ selectedFile ? selectedFile.name : 'ភ្ជាប់ឯកសារពិភាក្សា (PDF)...' }}
                                        <input type="file" class="d-none" @change="handleFileChange" accept=".pdf">
                                    </label>
                                    <i class="bi bi-cloud-arrow-up text-muted"></i>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="khmer-font small fw-bold text-muted mb-2 d-block">កម្រិតអាទិភាព</label>
                                <div class="d-flex gap-4">
                                    <div v-for="color in COLOR_OPTIONS" :key="color.id" 
                                        class="d-flex align-items-center cursor-pointer" @click="editingItem.color_id = color.id">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                            :style="{ width: '24px', height: '24px', backgroundColor: color.hex, border: editingItem.color_id === color.id ? '2px solid #ccc' : 'none' }">
                                            <i v-if="editingItem.color_id === color.id" class="bi bi-check text-white"></i>
                                        </div>
                                        <span class="khmer-font small" :class="{'fw-bold': editingItem.color_id === color.id}">{{ color.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 d-flex justify-content-between align-items-center border-top">
                        <button type="button" class="btn btn-link text-decoration-none text-muted khmer-font p-0" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> បោះបង់
                        </button>
                        
                        <button class="btn khmer-font px-5 py-2 rounded-3 shadow-sm text-white border-0" 
                                :disabled="isSaving" 
                                @click="updateMeeting"
                                :style="{ background: activeGradient }">
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

    // --- Constants (Matching Create Form) ---
    const TABS = [
        { id: 'meeting', label: 'កិច្ចប្រជុំ', icon: 'bi bi-camera-video', theme: '#e54d42', gradient: 'linear-gradient(135deg, #ff6b6b, #e54d42)' },
        { id: 'appointment', label: 'ការណាត់', icon: 'bi bi-calendar-event', theme: '#4285f4', gradient: 'linear-gradient(135deg, #6ab0ff, #4285f4)' },
        { id: 'task', label: 'ការងារ', icon: 'bi bi-check2-circle', theme: '#34a853', gradient: 'linear-gradient(135deg, #51cf66, #34a853)' }
    ]

    const COLOR_OPTIONS = [
        { id: 'red', hex: '#ff6b6b', label: 'បន្ទាន់' },
        { id: 'yellow', hex: '#fcc419', label: 'មធ្យម' },
        { id: 'green', hex: '#51cf66', label: 'ធម្មតា' }
    ]

    // --- Computed Styling ---
    const activeTab = computed(() => TABS.find(t => t.id === editingItem.value.type) || TABS[0])
    const activeTheme = computed(() => activeTab.value.theme)
    const activeGradient = computed(() => activeTab.value.gradient)
    

    // --- Style & Format Helpers ---
    const getBadgeClass = (color) => ({
        'bg-success-subtle text-success': color === 'green',
        'bg-danger-subtle text-danger': color === 'red',
        'bg-warning-subtle text-warning': color === 'yellow',
        'bg-primary-subtle text-primary': !color || color === 'blue'
    })

    const getBorderClass = (color) => ({
        'border-success': color === 'green',
        'border-danger': color === 'red',
        'border-warning': color === 'yellow',
        'border-primary': !color || color === 'blue'
    })

    const toKhmerNum = (num) => {
        if (!num) return ''
        const kh = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩']
        return num.toString().replace(/\d/g, d => kh[d])
    }

    // Returns AM/PM for the time inputs in the pill
    const getAMPM = (timeStr) => {
        if (!timeStr) return 'AM';
        const hour = parseInt(timeStr.split(':')[0]);
        return hour >= 12 ? 'PM' : 'AM';
    }




    const showUserDropdown = ref(false)
const userSearchQuery = ref('')
const allUsers = ref([]) // From API
const isFetchingUsers = ref(false)

// Fetch Users from API
const fetchApiUsers = async () => {
    isFetchingUsers.value = true;
    try {
        const res = await api.get('/users?per_page=100');
        
        const usersArray = res.data?.data || [];

        if (Array.isArray(usersArray)) {
            allUsers.value = usersArray.map(u => ({
                id: u.id,
                name: u.name,
                email: u.email,
                avatar: u.avatar_url
            }));
        }
    } catch (error) {
        console.error("User Fetch Error:", error.response?.data?.message || error.message);
    } finally {
        isFetchingUsers.value = false;
    }
}

const filteredUsers = computed(() => {
    if (!allUsers.value) return [];
    
    // Normalize search query
    const query = userSearchQuery.value.toLowerCase().trim();
    
    // If no search, show everyone
    if (!query) return allUsers.value;

    return allUsers.value.filter(u => 
        u.name?.toLowerCase().includes(query) || 
        u.email?.toLowerCase().includes(query)
    );
});


// Toggle Dropdown and Load Users
const toggleDropdown = () => {
    showUserDropdown.value = !showUserDropdown.value
    if (showUserDropdown.value && allUsers.value.length === 0) {
        fetchApiUsers()
    }
}

// Filter users based on search
// const filteredUsers = computed(() => {
//     return allUsers.value.filter(u => 
//         u.name.toLowerCase().includes(userSearchQuery.value.toLowerCase())
//     )
// })

// Check if user is already in the participants list
const isUserSelected = (user) => {
    return editingItem.value.participants?.some(p => p.id === user.id)
}

// Add/Remove user from selection
const toggleUserSelection = (user) => {
    if (!editingItem.value.participants) {
        editingItem.value.participants = []
    }

    const index = editingItem.value.participants.findIndex(p => p.id === user.id)
    if (index > -1) {
        editingItem.value.participants.splice(index, 1) // Remove
    } else {
        editingItem.value.participants.push(user) // Add
    }
}




    // Optional: Close dropdown when clicking outside
    onMounted(() => {
        window.addEventListener('click', (e) => {
            const header = document.querySelector('.pill-multiselect-header');
            if (header && !header.contains(e.target)) {
                showUserDropdown.value = false;
            }
        });
    });

    // --- Logic ---
    const fetchMeetings = async (page = 1) => {
        isLoading.value = true
        try {
            const data = await ScheduleService.getAll(page, searchQuery.value)
            meetings.value = data.data
            pagination.value = data.meta
            currentPage.value = data.meta.current_page
        } catch (error) {
            console.error('Fetch Error:', error)
        } finally {
            isLoading.value = false
        }
    }

const openEditModal = (item) => {
    // 1. Assign/Clone the data FIRST
    editingItem.value = JSON.parse(JSON.stringify(item));

    // 2. Now log it (it will show the data from the item)
    console.log('Current Participants:', editingItem.value.participants);

    // 3. Ensure it is an array for the multiselect logic
    if (!editingItem.value.participants) {
        editingItem.value.participants = [];
    } else if (typeof editingItem.value.participants === 'string') {
        try {
            editingItem.value.participants = JSON.parse(editingItem.value.participants);
        } catch (e) {
            editingItem.value.participants = [];
        }
    }

    // Reset UI states
    showUserDropdown.value = false;
    selectedFile.value = null;

    // Show Bootstrap Modal
    if (!modalInstance && modalElement.value) {
        modalInstance = new Modal(modalElement.value);
    }
    modalInstance?.show();
};

    const handleFileChange = (event) => {
        const file = event.target.files[0]
        if (file && file.type === 'application/pdf') {
            selectedFile.value = file
        } else {
            Swal.fire({ icon: 'error', title: 'ឯកសារមិនត្រឹមត្រូវ', text: 'សូមជ្រើសរើសឯកសារ PDF ប៉ុណ្ណោះ' })
        }
    }

    const updateMeeting = async () => {
        isSaving.value = true
        try {
            const data = new FormData()
            data.append('_method', 'PUT') 

            // 1. Append standard fields
            Object.keys(editingItem.value).forEach(key => {
                const value = editingItem.value[key]
                // Skip UI helpers and the participants objects
                if (!['attachment', 'participants', 'participants_display', 'participant_emails'].includes(key) && value !== null) {
                    data.append(key, value)
                }
            })

            // 2. Append Participant IDs as an array (The "Standard" Way)
            if (editingItem.value.participants && editingItem.value.participants.length > 0) {
                editingItem.value.participants.forEach((user, index) => {
                    data.append(`participant_ids[${index}]`, user.id)
                })
            }

            // 3. Append File
            if (selectedFile.value) data.append('attachment', selectedFile.value)

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
            Swal.fire({ 
                icon: 'error', 
                title: 'បរាជ័យ', 
                text: error.response?.data?.message || 'មានកំហុសបច្ចេកទេស' 
            })
        } finally {
            isSaving.value = false
        }
    }

    

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

    onMounted(() => fetchMeetings())
</script>