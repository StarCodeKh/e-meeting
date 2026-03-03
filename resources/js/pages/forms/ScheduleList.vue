<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        <div class="container-fluid bg-light min-vh-100">
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
                            <i class="bi bi-plus-circle me-2"></i> បង្កើតកិច្ចប្រជុំថ្មី
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="isLoading" class="row">
                <div v-for="n in 6" :key="n" class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 p-4 skeleton-card animate-pulse">
                        <div class="skeleton-line mb-3" style="width: 30%;"></div>
                        <div class="skeleton-line mb-2" style="width: 80%;"></div>
                        <div class="skeleton-line mb-4" style="width: 60%;"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="meetings.length === 0" class="col-12 text-center">
                <div class="card border-0 shadow-sm rounded-3 p-5 bg-white">
                    <div class="mb-4">
                        <div class="display-1 text-muted opacity-25">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                    <h4 class="khmer-font fw-bold text-dark">រកមិនឃើញលទ្ធផល!</h4>
                    <p class="khmer-font text-muted mb-4">
                        មិនមានកិច្ចប្រជុំណាដែលត្រូវនឹងពាក្យថា <span class="text-primary fw-bold">"{{ searchQuery }}"</span> ឡើយ។
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-light khmer-font px-4 rounded-3 border" @click="searchQuery = ''; fetchMeetings(1)">
                            បង្ហាញទាំងអស់ទ្បើងវិញ
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="row">
                <div v-for="item in meetings" :key="item.id" class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100 meeting-card border-start border-4" 
                         :style="{ borderLeftColor: getPriorityColor(item.color_id) + ' !important' }">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge rounded-3 px-3 py-2 khmer-font shadow-sm" :style="getBadgeStyle(item.color_id)">
                                        <i class="bi bi-door-open me-1"></i> {{ item.room }}
                                    </span>
                                    <span class="small text-muted khmer-font fw-bold border-start ps-2">
                                        <i class="bi bi-calendar-event me-1 text-primary"></i> {{ toKhmerNum(item.date) }}
                                    </span>
                                </div>
                                
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-3 action-btn" type="button" data-bs-toggle="dropdown">
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
                            <p class="khmer-font text-muted small mb-3 line-clamp-3">
                                {{ item.description || 'មិនមានការពិពណ៌នា...' }}
                            </p>

                            <div class="mb-4 mt-auto">
                                <div class="small text-muted khmer-font mb-2">
                                    <i class="bi bi-clock-fill me-1 opacity-75" :style="{ color: getPriorityColor(item.color_id) }"></i> 
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
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 650px;">
                    <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden" :style="{ borderTop: `6px solid ${activeTheme}` }">
                        <div class="d-flex bg-white border-bottom p-2 gap-2 justify-content-center">
                            <button v-for="tab in TABS" :key="tab.id" class="btn border-0 rounded-3 px-4 py-2 khmer-font transition-all d-flex align-items-center justify-content-center flex-grow-1" :style="editingItem.type === tab.id ? { background: tab.theme, color: 'white' } : { color: '#666', background: 'transparent' }" @click="editingItem.type = tab.id">
                                <i :class="tab.icon" class="me-2"></i> 
                                {{ tab.label }}
                            </button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <input v-model="editingItem.title" type="text" class="form-control khmer-font fs-4 fw-bold border-0 border-bottom bg-transparent rounded-0 px-0 shadow-none pb-2 transition-all" :style="{ borderBottomColor: editingItem.title ? activeTheme : '#eee' }" placeholder="បញ្ចូលចំណងជើង...">
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="bg-light p-2 rounded-3 d-flex flex-column flex-md-row align-items-center px-3 border gap-2">
                                        <div class="d-flex align-items-center w-100 w-md-auto">
                                            <i class="bi bi-calendar3 me-2" :style="{ color: activeTheme }"></i>
                                            <DatePicker v-model="editingItem.date" :popover="{ visibility: 'click' }">
                                                <template #default="{ inputValue, inputEvents }">
                                                    <input class="form-control form-control-sm border-0 bg-transparent shadow-none khmer-font p-0" :value="inputValue" v-on="inputEvents" readonly />
                                                </template>
                                            </DatePicker>
                                        </div>
                                        <div class="vr mx-2 opacity-25 d-none d-md-block" style="height: 20px;"></div>
                                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                                            <input v-model="editingItem.start_time" type="time" class="border-0 bg-transparent fw-bold small">
                                            <span class="badge rounded-2" :style="{ background: activeTheme }">{{ getAMPM(editingItem.start_time) }}</span>
                                            <span class="mx-1 text-muted">-</span>
                                            <input v-model="editingItem.end_time" type="time" class="border-0 bg-transparent fw-bold small">
                                            <span class="badge rounded-2" :style="{ background: activeTheme }">{{ getAMPM(editingItem.end_time) }}</span>
                                        </div>
                                    </div>
                                </div>
  
                                <div class="col-12 position-relative">
                                    <div class="input-group bg-light rounded-3 border pill-multiselect-header cursor-pointer transition-all" @click="toggleDropdown">
                                        <span class="input-group-text border-0 bg-transparent">
                                            <i class="bi bi-people transition-all" :style="{ color: editingItem.participants?.length ? activeTheme : '#6c757d' }"></i>
                                        </span>
                                        <div class="form-control khmer-font border-0 bg-transparent shadow-none py-2 text-truncate">
                                            {{ participantDisplayNames }}
                                        </div>
                                        <i class="bi px-3 transition-all" :class="showUserDropdown ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem; color: #6c757d"></i>
                                    </div>

                                    <div v-if="showUserDropdown" class="bg-white rounded-3 border mt-1 w-100 shadow-sm position-absolute z-3 overflow-hidden animate__animated animate__fadeIn animate__faster">
                                        <div class="p-2 border-bottom bg-light">
                                            <input v-model="userSearchQuery" type="text" class="form-control form-control-sm khmer-font shadow-none border-0 bg-white" placeholder="ស្វែងរកឈ្មោះ ឬអ៊ីមែល..." @click.stop>
                                        </div>

                                        <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center justify-content-between cursor-pointer hover-bg-light transition-all" @click.stop="toggleSelectAll">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2 transition-all" 
                                                    :style="{ 
                                                        width: '24px', 
                                                        height: '24px', 
                                                        background: isAllSelected ? activeTheme : '#eee', 
                                                        color: isAllSelected ? 'white' : '#666' 
                                                    }">
                                                    <i v-if="isAllSelected" class="bi bi-check-all"></i>
                                                    <i v-else class="bi bi-people-fill" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <span class="khmer-font small fw-bold" :style="isAllSelected ? { color: activeTheme } : { color: '#666' }">
                                                    {{ isAllSelected ? 'ដកចេញទាំងអស់' : 'ជ្រើសរើសទាំងអស់' }}
                                                </span>
                                            </div>
                                            <span class="badge rounded-pill bg-secondary opacity-75" style="font-size: 0.65rem;">{{ filteredUsers.length }} នាក់</span>
                                        </div>

                                        <div class="overflow-auto custom-scrollbar" style="max-height: 200px;">
                                            <div v-for="user in filteredUsers" :key="user.id" 
                                                class="d-flex align-items-center px-3 py-2 border-bottom-faint cursor-pointer hover-bg-light transition-all" 
                                                @click.stop="toggleUserSelection(user)">
                                                
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0 transition-all" 
                                                    :style="{ 
                                                        width: '32px', 
                                                        height: '32px', 
                                                        background: isUserSelected(user) ? activeTheme : '#f0f0f0', 
                                                        color: isUserSelected(user) ? 'white' : '#666',
                                                        border: isUserSelected(user) ? `2px solid ${activeTheme}` : '2px solid transparent'
                                                    }">
                                                    <img v-if="user.image" :src="user.image" class="w-100 h-100 object-fit-cover" @error="user.image = null">
                                                    <span v-else class="fw-bold" style="font-size: 0.8rem;">{{ user.name?.charAt(0) }}</span>
                                                </div>

                                                <div class="flex-grow-1 khmer-font small text-dark fw-bold text-truncate" :style="isUserSelected(user) ? { color: activeTheme } : {}">
                                                    {{ user.name }}
                                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;">{{ user.email }}</div>
                                                </div>

                                                <i v-if="isUserSelected(user)" class="bi bi-check-lg ms-2" :style="{ color: activeTheme }"></i>
                                            </div>

                                            <div v-if="filteredUsers.length === 0" class="p-4 text-center text-muted khmer-font small">
                                                <i class="bi bi-search d-block fs-4 mb-2"></i>
                                                រកមិនឃើញអ្នកប្រើប្រាស់ឡើយ
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div :class="editingItem.type === 'meeting' ? 'col-md-6' : 'col-12'">
                                    <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                        <i class="bi bi-geo-alt me-2" :style="{ color: activeTheme }"></i>
                                        <input v-model="editingItem.location" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="ទីតាំង">
                                    </div>
                                </div>
                                <div class="col-md-3" v-if="editingItem.type === 'meeting'">
                                    <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                        <i class="bi bi-layers me-2" :style="{ color: activeTheme }"></i>
                                        <input v-model="editingItem.floor" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="ជាន់">
                                    </div>
                                </div>
                                <div class="col-md-3" v-if="editingItem.type === 'meeting'">
                                    <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                        <i class="bi bi-door-open me-2" :style="{ color: activeTheme }"></i>
                                        <input v-model="editingItem.room" type="text" class="form-control border-0 bg-transparent shadow-none khmer-font" placeholder="បន្ទប់">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-start">
                                        <i class="bi bi-card-text mt-2 me-2" :style="{ color: activeTheme }"></i>
                                        <textarea v-model="editingItem.description" rows="2" class="form-control khmer-font border-0 bg-transparent shadow-none py-2" placeholder="ពណ៌នាការងារ..."></textarea>
                                    </div>
                                </div>

                                <template v-if="editingItem.type === 'meeting'">
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100">
                                            <i class="bi bi-link-45deg me-2" :style="{ color: activeTheme }"></i>
                                            <input v-model="editingItem.link" type="url" class="form-control border-0 bg-transparent shadow-none small" placeholder="លីងតំណភ្ជាប់">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 border p-1 px-3 d-flex align-items-center h-100 transition-all" :style="selectedFile ? { borderColor: activeTheme, backgroundColor: '#fff' } : {}">
                                            <i class="bi bi-file-earmark-pdf me-2" :style="{ color: (selectedFile || editingItem.attachment) ? activeTheme : '#6c757d' }"></i>
                                            <label class="form-control border-0 bg-transparent shadow-none mb-0 flex-grow-1 cursor-pointer khmer-font text-muted p-0 small text-truncate">
                                                <span v-if="selectedFile" :style="{ color: activeTheme }">{{ selectedFile.name }}</span>
                                                <span v-else-if="editingItem.attachment" class="text-dark">{{ editingItem.attachment.split('/').pop() }}</span>
                                                <span v-else>ភ្ជាប់ PDF...</span>
                                                <input type="file" class="d-none" @change="handleFileChange" accept=".pdf">
                                            </label>
                                            <i v-if="selectedFile" class="bi bi-x-circle text-danger ms-1 cursor-pointer" @click.stop="selectedFile = null"></i>
                                        </div>
                                    </div>
                                </template>

                                <div class="col-12 mt-2">
                                    <div class="p-3 border rounded-3 bg-white">
                                        <label class="khmer-font small fw-bold text-muted mb-2 d-block">កម្រិតអាទិភាព</label>
                                        <div class="d-flex justify-content-between gap-2">
                                            <div v-for="color in COLOR_OPTIONS" :key="color.id" 
                                                class="d-flex align-items-center cursor-pointer p-2 px-3 rounded-2 transition-all flex-grow-1 justify-content-center" 
                                                :style="editingItem.color_id === color.id ? { background: color.hex + '15' } : {}"
                                                @click="editingItem.color_id = color.id">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center transition-all" 
                                                    :style="{ width: '14px', height: '14px', backgroundColor: color.hex }">
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
                            <button type="button" class="btn btn-link text-decoration-none text-muted khmer-font p-0" data-bs-dismiss="modal">បោះបង់</button>
                            <button class="btn khmer-font px-5 py-2 rounded-3 text-white border-0 shadow-sm transition-all" :disabled="isSaving" @click="updateMeeting" :style="{ background: activeGradient, opacity: isSaving ? 0.7 : 1 }">
                                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                                {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <nav v-if="pagination.last_page > 1" class="mt-5 d-flex flex-column align-items-center gap-3">
                <div class="khmer-font text-muted small">
                    ទំព័រទី {{ toKhmerNum(currentPage) }} នៃ {{ toKhmerNum(pagination.last_page) }}
                </div>
                <ul class="pagination gap-2 mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <button class="page-link rounded-3 border-0 shadow-sm transition-all" @click="changePage(currentPage - 1)">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </li>
                    <li v-for="page in pagination.links?.slice(1, -1)" :key="page.label" class="page-item d-none d-md-block" :class="{ active: page.active }">
                        <button class="page-link rounded-3 border-0 shadow-sm khmer-font transition-all" :style="page.active ? { background: activeGradient, color: 'white' } : {}" @click="changePage(parseInt(page.label))">
                            {{ toKhmerNum(page.label) }}
                        </button>
                    </li>
                    <li class="page-item d-md-none active">
                        <button class="page-link rounded-3 border-0 shadow-sm khmer-font" :style="{ background: activeGradient, color: 'white' }">
                            {{ toKhmerNum(currentPage) }}
                        </button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === pagination.last_page }">
                        <button class="page-link rounded-3 border-0 shadow-sm transition-all" @click="changePage(currentPage + 1)">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
    import { ScheduleService } from '@/services/ScheduleService'
    import { getScheduleFormOptions } from '@/services/ScheduleTypes';
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'
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
    let searchTimeout = null

    const showUserDropdown = ref(false)
    const userSearchQuery = ref('')
    const allUsers = ref([])
    const isFetchingUsers = ref(false)

    const scheduleTypes = ref([])
    const priorities = ref([])

    // --- Dynamic Options (Computed from Database) ---
    const TABS = computed(() => {
        if (!scheduleTypes.value.length) return [];
        return scheduleTypes.value.map(type => ({
            id: type.slug,
            label: type.name,
            theme: type.color_hex,
            gradient: type.color_gradient,
            icon: type.icon
        }));
    });

    const COLOR_OPTIONS = computed(() => {
        if (!priorities.value.length) return [];
        return priorities.value.map(p => ({
            id: p.slug,
            hex: p.color_hex,
            label: p.name
        }));
    });

    // --- UI Computed Properties ---
    const activeTheme = computed(() => {
        const tab = TABS.value.find(t => t.id === editingItem.value.type);
        return tab ? tab.theme : '#6c757d';
    });

    const activeGradient = computed(() => {
        const tab = TABS.value.find(t => t.id === editingItem.value.type);
        return tab ? tab.gradient : 'linear-gradient(135deg, #6c757d, #495057)';
    });

    const filteredUsers = computed(() => {
        if (!allUsers.value) return []
        const query = userSearchQuery.value.toLowerCase().trim()
        return allUsers.value.filter(u => 
            u.name?.toLowerCase().includes(query) || 
            u.email?.toLowerCase().includes(query)
        )
    })

    const participantDisplayNames = computed(() => {
        if (!editingItem.value.participants?.length) return 'ជ្រើសរើសអ្នកចូលរួម...';
        return editingItem.value.participants.map(email => {
            const found = allUsers.value.find(u => u.email === email);
            return found ? found.name : email;
        }).join(', ');
    });

    // --- Check All Logic ---
    const isAllSelected = computed(() => {
        if (filteredUsers.value.length === 0) return false;
        return filteredUsers.value.every(user => isUserSelected(user));
    });

    const toggleSelectAll = () => {
        if (!editingItem.value.participants) editingItem.value.participants = [];

        if (isAllSelected.value) {
            // ដកចេញទាំងអស់ (តែក្នុងចំណោមអ្នកដែលកំពុង Filter ឃើញប៉ុណ្ណោះ)
            const filteredEmails = filteredUsers.value.map(u => u.email);
            editingItem.value.participants = editingItem.value.participants.filter(
                email => !filteredEmails.includes(email)
            );
        } else {
            // បន្ថែមចូលទាំងអស់
            filteredUsers.value.forEach(user => {
                if (!isUserSelected(user)) {
                    editingItem.value.participants.push(user.email);
                }
            });
        }
    };

    // --- Core Methods ---
    const fetchMeetings = async (page = 1) => {
        isLoading.value = true
        try {
            const data = await ScheduleService.getAll(page, searchQuery.value)
            meetings.value = data.data
            pagination.value = data.meta
            currentPage.value = data.meta.current_page
        } catch (e) { 
            console.error("Fetch Error:", e) 
        } finally { 
            isLoading.value = false 
        }
    }

    const handleSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage.value = 1;
            fetchMeetings(1);
        }, 500);
    };

    const changePage = (page) => {
        if (page >= 1 && page <= pagination.value.last_page) {
            fetchMeetings(page);
        }
    };

    // --- User Selection Methods ---
    const fetchApiUsers = async () => {
        isFetchingUsers.value = true
        try {
            const res = await api.get('/users?per_page=100')
            allUsers.value = (res.data?.data || []).map(u => ({
                ...u,
                image: u.avatar_url || u.image || null 
            }))
        } catch (error) {
            console.error("User Fetch Error:", error)
        } finally {
            isFetchingUsers.value = false
        }
    }

    const toggleDropdown = () => {
        showUserDropdown.value = !showUserDropdown.value
        if (showUserDropdown.value && allUsers.value.length === 0) fetchApiUsers()
    }

    const toggleUserSelection = (user) => {
        if (!editingItem.value.participants) {
            editingItem.value.participants = [];
        }
        const index = editingItem.value.participants.indexOf(user.email);
        if (index > -1) {
            editingItem.value.participants.splice(index, 1);
        } else {
            editingItem.value.participants.push(user.email);
        }
    };

    const isUserSelected = (user) => {
        return editingItem.value.participants?.includes(user.email);
    };

    const openEditModal = (item) => {
        editingItem.value = JSON.parse(JSON.stringify(item));
        selectedFile.value = null;

        if (editingItem.value.date && typeof editingItem.value.date === 'string') {
            const [year, month, day] = editingItem.value.date.split('-').map(Number);
            editingItem.value.date = new Date(year, month - 1, day);
        }

        const rawParticipants = item.participant_emails || item.participants || [];
        
        editingItem.value.participants = Array.isArray(rawParticipants)
            ? rawParticipants.map(p => (typeof p === 'object' ? p.email : p)) 
                            .filter(e => e && e.includes('@')) 
            : [];

        if (allUsers.value.length === 0) fetchApiUsers();
        showUserDropdown.value = false;
        userSearchQuery.value = '';

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
                const skipKeys = ['attachment', 'participants', 'participant_emails', 'created_at', 'updated_at'];
                if (!skipKeys.includes(key) && value !== null && value !== undefined) {
                    data.append(key, value)
                }
            })
            
            if (editingItem.value.participants && editingItem.value.participants.length > 0) {
                editingItem.value.participants.forEach((email) => {
                    data.append('participants[]', email); 
                });
            } else {
                data.append('participants[]', ''); 
            }

            if (selectedFile.value) {
                data.append('attachment', selectedFile.value)
            }

            await ScheduleService.update(editingItem.value.id, data)
            
            modalInstance?.hide()
            await fetchMeetings(currentPage.value)

            Swal.fire({
                icon: 'success',
                title: 'កែប្រែទិន្នន័យជោគជ័យ',
                toast: true,
                position: 'top-end',
                timer: 2500,
                customClass: { popup: 'khmer-font' }
            })

        } catch (error) {
            console.error("❌ Update Error Detail:", error.response?.data || error)
        } finally {
            isSaving.value = false
        }
    }

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (!file) return;
        if (file.type !== 'application/pdf') {
            Swal.fire({ icon: 'error', title: 'សូមជ្រើសរើស PDF ប៉ុណ្ណោះ', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, customClass: { popup: 'khmer-font' } });
            event.target.value = ''; return;
        }
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'ឯកសារមិនអាចលើសពី 5MB', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, customClass: { popup: 'khmer-font' } });
            event.target.value = ''; return;
        }
        selectedFile.value = file;
    };

    // --- Helpers ---
    const toKhmerNum = (n) => n?.toString().replace(/\d/g, d => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d])
    const getAMPM = (t) => t && parseInt(t.split(':')[0]) >= 12 ? 'PM' : 'AM'

    const getPriorityColor = (colorId) => {
        const priority = priorities.value.find(p => p.slug === colorId);
        return priority ? priority.color_hex : '#6c757d'; 
    };

    const getBadgeStyle = (colorId) => {
        const hex = getPriorityColor(colorId);
        return {
            backgroundColor: `${hex}15`,
            color: hex,
            border: `1px solid ${hex}30`
        };
    };

    const confirmDelete = async (id) => {
        if (!id) return
        const result = await Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "ទិន្នន័យនេះនឹងត្រូវលុបចេញពីប្រព័ន្ធ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'យល់ព្រមលុប',
            cancelButtonText: 'បោះបង់',
            customClass: { popup: 'khmer-font' }
        })

        if (result.isConfirmed) {
            try {
                await ScheduleService.delete(id)
                fetchMeetings(currentPage.value)
                Swal.fire({ icon: 'success', title: 'លុបចេញជោគជ័យ', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
            } catch (error) {
                console.error(error);
            }
        }
    }
    
    // --- Lifecycle Hooks ---
    const handleClickOutside = (e) => {
        if (!e.target.closest('.pill-multiselect-header') && !e.target.closest('.bg-white.rounded-3.border.mt-1')) {
            showUserDropdown.value = false
        }
    };

    onMounted(async () => {
        await fetchMeetings()
        fetchApiUsers() 

        try {
            const response = await getScheduleFormOptions(); 
            scheduleTypes.value = response.types || [];
            priorities.value = response.priorities || [];

            if (scheduleTypes.value.length > 0 && !editingItem.value.type) {
                editingItem.value.type = scheduleTypes.value[0].slug;
            }
            if (priorities.value.length > 0 && !editingItem.value.color_id) {
                editingItem.value.color_id = priorities.value[0].slug;
            }
        } catch (err) {
            console.error("Error loading schedule options:", err);
        }

        window.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
        window.removeEventListener('click', handleClickOutside)
    })

</script>

<style scoped>
    @import "@/css/schedule-list.css";
</style>

