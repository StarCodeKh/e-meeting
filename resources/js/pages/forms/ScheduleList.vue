<template>
    <div class="container-fluid py-4 bg-light min-vh-100">
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4 card-header-custom border-top border-primary border-5">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <h4 class="khmer-font fw-bold mb-1 text-primary">បញ្ជីកាលវិភាគប្រជុំ</h4>
                    <p class="text-muted small mb-0 khmer-font">
                        លទ្ធផលសរុប៖ <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ toKhmerNum(pagination.total || 0) }} កិច្ចប្រជុំ</span>
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
                <div class="card border-0 shadow-sm rounded-4 p-4 skeleton-card animate-pulse">
                    <div class="skeleton-line mb-3" style="width: 30%;"></div>
                    <div class="skeleton-line mb-2" style="width: 80%;"></div>
                    <div class="skeleton-line mb-4" style="width: 60%;"></div>
                </div>
            </div>
        </div>

        <div v-else class="row g-3">
            <div v-for="item in meetings" :key="item.id" class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 meeting-card border-start border-4" :class="getBorderClass(item.color_id)">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge rounded-pill px-3 py-2 khmer-font shadow-sm" :class="getBadgeClass(item.color_id)">
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
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 p-4 pb-0">
                        <h5 class="modal-title khmer-font fw-bold text-primary">កែសម្រួលព័ត៌មាន</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light khmer-font px-4" data-bs-dismiss="modal">បោះបង់</button>
                        <button class="btn btn-primary khmer-font px-4" @click="updateMeeting">រក្សាទុក</button>
                    </div>
                </div>
            </div>
        </div>

        <nav v-if="pagination.last_page > 1" class="mt-5 d-flex justify-content-center">
            <ul class="pagination shadow-sm rounded-4 overflow-hidden border-0 bg-white">
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
    import { ref, onMounted } from 'vue'
    import { useRouter } from 'vue-router'
    import { ScheduleService } from '@/services/ScheduleService'
    import Swal from 'sweetalert2'

    import { Modal } from 'bootstrap'
    import 'bootstrap/dist/css/bootstrap.min.css'

    const router = useRouter()

    // --- State Management ---
    const meetings = ref([])
    const pagination = ref({})
    const currentPage = ref(1)
    const isLoading = ref(false)
    const searchQuery = ref('')
    const editingItem = ref({})

    const modalElement = ref(null)
    let modalInstance = null 

    // --- Helpers ---
    const toKhmerNum = (num) => {
        if (num === null || num === undefined) return ''
        const kh = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩']
        return num.toString().replace(/\d/g, d => kh[d])
    }

    const getBadgeClass = (color) => ({
        'bg-success-subtle text-success': color === 'green',
        'bg-danger-subtle text-danger': color === 'red',
        'bg-warning-subtle text-warning': color === 'yellow',
        'bg-primary-subtle text-primary': !color
    })

    const getBorderClass = (color) => ({
        'border-success': color === 'green',
        'border-danger': color === 'red',
        'border-warning': color === 'yellow',
        'border-primary': !color
    })

    // --- Core Logic using Service ---
    const fetchMeetings = async (page = 1) => {
        isLoading.value = true
        try {
            // CALL SERVICE
            const data = await ScheduleService.getAll(page, searchQuery.value)
            
            meetings.value = data.data
            pagination.value = data.meta
            currentPage.value = data.meta.current_page
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'កំហុស!', text: 'មិនអាចទាញយកទិន្នន័យបានទេ', customClass: { popup: 'khmer-font' }})
        } finally {
            isLoading.value = false
        }
    }

    const handleSearch = () => {
        clearTimeout(window.searchTimeout)
        window.searchTimeout = setTimeout(() => fetchMeetings(1), 500)
    }

    const openEditModal = (item) => {
        editingItem.value = { ...item } 
        if (!modalInstance && modalElement.value) {
            modalInstance = new Modal(modalElement.value)
        }
        modalInstance?.show()
    }

    const updateMeeting = async () => {
        try {
            // CALL SERVICE
            await ScheduleService.update(editingItem.value.id, editingItem.value)
            
            modalInstance?.hide()
            Swal.fire({ 
                icon: 'success', 
                title: 'ជោគជ័យ', 
                text: 'កែសម្រួលបានជោគជ័យ', 
                timer: 1500, 
                showConfirmButton: false,
                customClass: { popup: 'khmer-font' }
            })
            fetchMeetings(currentPage.value)
        } catch (error) {
            const message = error.response?.data?.message || 'មិនអាចរក្សាទុកបានទេ'
            Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: message, customClass: { popup: 'khmer-font' }})
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
                // CALL SERVICE
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

<style scoped>
    @import "@/css/schedule-list.css";
</style>