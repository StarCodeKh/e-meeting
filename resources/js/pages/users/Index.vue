<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        <div class="container-fluid bg-light min-vh-100 p-3 p-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-4 card-header-custom border-top border-primary border-5">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <h4 class="khmer-font fw-bold mb-1 text-primary">គ្រប់គ្រងអ្នកប្រើប្រាស់</h4>
                        <p class="text-muted small mb-0 khmer-font">
                            សរុប៖ <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ toKhmerNum(pagination.total || 0) }} នាក់</span>
                        </p>
                    </div>
                    <div class="col-md-5">
                        <div class="search-box position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input v-model="searchQuery" type="text" class="form-control khmer-font py-2 ps-5 border-0 shadow-none bg-light rounded-3" 
                                placeholder="ស្វែងរកតាមឈ្មោះ ឬអុីមែល..." @input="handleSearch">
                        </div>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <button class="btn btn-primary khmer-font rounded-3 shadow-sm px-4 py-2 w-100 w-md-auto transition-all" @click="openEditModal()">
                            <i class="bi bi-person-plus-fill me-2"></i> បន្ថែមសមាជិកថ្មី
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="isLoading" class="row">
                <div v-for="n in 6" :key="n" class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 skeleton-card animate-pulse">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-light rounded-circle" style="width: 60px; height: 60px;"></div>
                            <div class="flex-grow-1">
                                <div class="bg-light rounded-2 mb-2" style="width: 60%; height: 15px;"></div>
                                <div class="bg-light rounded-2" style="width: 40%; height: 10px;"></div>
                            </div>
                        </div>
                        <div class="bg-light rounded-2 mb-2" style="width: 100%; height: 12px;"></div>
                        <div class="bg-light rounded-2" style="width: 80%; height: 12px;"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="users.length === 0" class="text-center py-5">
                <div class="py-5">
                    <i class="bi bi-people text-muted opacity-25" style="font-size: 5rem;"></i>
                    <h5 class="khmer-font text-muted mt-3">មិនមានទិន្នន័យអ្នកប្រើប្រាស់ឡើយ</h5>
                </div>
            </div>

            <div v-else class="row">
                <div v-for="user in users" :key="user.id" class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 user-card border-top border-3 transition-all" :class="getRoleBorder(user.role_id)">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img :src="user.avatar || 'https://ui-avatars.com/api/?name='+user.name+'&background=random'" class="rounded-circle border border-2 shadow-sm object-fit-cover" width="55" height="55">
                                        <span class="position-absolute bottom-0 end-0 border border-white border-2 rounded-circle shadow-sm" 
                                            :style="{ width: '14px', height: '14px', backgroundColor: user.active ? '#10b981' : '#94a3b8' }"></span>
                                    </div>
                                    <div class="text-truncate">
                                        <h6 class="khmer-font fw-bold mb-0 text-dark text-truncate" style="max-width: 150px;">{{ user.name }}</h6>
                                        <span class="extra-small text-muted">{{ user.email }}</span>
                                    </div>
                                </div>
                                
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle action-btn" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 khmer-font p-2">
                                        <li><button class="dropdown-item py-2 rounded-2 text-primary" @click="openEditModal(user)">
                                            <i class="bi bi-pencil-square me-2"></i> កែសម្រួល</button></li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li><button class="dropdown-item py-2 rounded-2 text-danger" @click="confirmDelete(user.id)">
                                            <i class="bi bi-trash3 me-2"></i> លុបចេញ</button></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge rounded-pill px-3 py-1 khmer-font fw-normal" :class="getRoleBadgeClass(user.role_id)">
                                    {{ user.role_name }}
                                </span>
                                <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-3 py-1 khmer-font fw-normal">
                                    <i class="bi bi-building-fill me-1 small"></i> {{ user.department }}
                                </span>
                            </div>

                            <div class="mt-auto pt-3 border-top border-light-subtle">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small text-muted khmer-font">
                                        <i class="bi bi-calendar-check me-1 text-primary"></i> ចូលរួម៖ <span class="text-dark fw-bold">{{ toKhmerNum(user.joined_date) }}</span>
                                    </div>
                                    <button @click="viewProfile(user)" class="btn btn-sm btn-outline-primary khmer-font rounded-pill px-3 py-1 border-0 bg-primary-subtle-hover">
                                        លម្អិត <i class="bi bi-chevron-right ms-1 small"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editUserModal" ref="modalElement" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 bg-primary p-4 position-relative">
                        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
                        <h5 class="modal-title text-white khmer-font fw-bold z-1">
                            {{ editingUser.id ? 'កែសម្រួលព័ត៌មាន' : 'បន្ថែមសមាជិកថ្មី' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white shadow-none z-1" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body p-4 bg-white">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img :src="avatarPreview || editingUser.avatar || 'https://ui-avatars.com/api/?name=User&background=eee'" 
                                    class="rounded-circle border border-4 border-white shadow p-1 object-fit-cover" 
                                    width="110" height="110">
                                <label for="avatarInput" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle border border-white border-3 shadow-sm p-2 cursor-pointer">
                                    <i class="bi bi-camera"></i>
                                    <input type="file" id="avatarInput" class="d-none" @change="handleAvatarChange" accept="image/*">
                                </label>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">ឈ្មោះពេញ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-person text-muted"></i></span>
                                    <input v-model="editingUser.name" type="text" class="form-control khmer-font bg-light border-0 py-2 shadow-none" placeholder="បញ្ចូលឈ្មោះ...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">អុីមែល</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input v-model="editingUser.email" type="email" class="form-control bg-light border-0 py-2 shadow-none" placeholder="example@domain.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">តួនាទី</label>
                                <select v-model="editingUser.role_id" class="form-select khmer-font bg-light border-0 py-2 shadow-none">
                                    <option value="1">អ្នកគ្រប់គ្រង (Admin)</option>
                                    <option value="2">អ្នកកែសម្រួល (Editor)</option>
                                    <option value="3">បុគ្គលិក (User)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">ស្ថានភាព</label>
                                <div class="p-2 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                    <label class="form-check-label khmer-font small text-muted" for="userStatus">
                                        {{ editingUser.active ? 'គណនីកំពុងដំណើរការ' : 'គណនីផ្អាកបណ្តោះអាសន្ន' }}
                                    </label>
                                    <div class="form-check form-switch m-0">
                                        <input v-model="editingUser.active" class="form-check-input cursor-pointer" type="checkbox" id="userStatus" style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0 bg-white d-flex justify-content-between">
                        <button class="btn btn-light khmer-font px-4 rounded-3 border-0" data-bs-dismiss="modal">បោះបង់</button>
                        <button class="btn btn-primary khmer-font px-5 py-2 rounded-3 shadow-sm d-flex align-items-center" 
                                :disabled="isSaving" @click="saveUser">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-cloud-arrow-up me-2"></i>
                            {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, watch, onMounted, onUnmounted } from 'vue'
    import { UserService } from '@/services/UserService'
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'
    import Swal from 'sweetalert2'
    import { Modal } from 'bootstrap'

    // --- 1. Dynamic State ---
    const users = ref([])
    const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
    const isLoading = ref(false)
    const searchQuery = ref('')
    const selectedRole = ref('') 
    const editingUser = ref({})
    const isSaving = ref(false)

    const avatarPreview = ref(null)
    const selectedAvatarFile = ref(null)
    const modalElement = ref(null)
    let modalInstance = null

    // --- 2. Standardized Constants ---
    const ROLES = [
        { id: 1, label: 'អ្នកគ្រប់គ្រង (Admin)', badge: 'bg-primary text-white', border: 'border-primary' },
        { id: 2, label: 'អ្នកកែសម្រួល (Editor)', badge: 'bg-info-subtle text-info', border: 'border-info' },
        { id: 3, label: 'បុគ្គលិក (Staff)', badge: 'bg-light text-muted', border: 'border-secondary' }
    ]

    // --- 3. Dynamic Helpers (Fixes Template Errors) ---
    const toKhmerNum = (n) => n?.toString().replace(/\d/g, d => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d])

    const getRoleBadgeClass = (id) => ROLES.find(r => r.id == id)?.badge || 'bg-light'

    const getRoleBorder = (id) => ROLES.find(r => r.id == id)?.border || 'border-light'

    // --- 4. Dynamic Fetch Logic ---
    const fetchUsers = async () => {
        isLoading.value = true
        try {
            const response = await UserService.getAll(pagination.value.current_page, searchQuery.value, {
                role_id: selectedRole.value || undefined
            })
            users.value = response.data
            pagination.value = response.meta
        } catch (e) {
            console.error("Fetch Failure", e)
        } finally {
            isLoading.value = false
        }
    }

    // --- 5. Reactive Watchers ---
    watch([() => pagination.value.current_page, selectedRole], () => fetchUsers())

    let searchTimer = null
    watch(searchQuery, () => {
        clearTimeout(searchTimer)
        searchTimer = setTimeout(() => {
            pagination.value.current_page = 1
            fetchUsers()
        }, 400)
    })

    // --- 6. Modal & Save Logic ---
    const openEditModal = (user = null) => {
        if (user) {
            editingUser.value = { ...user }
            avatarPreview.value = user.avatar 
        } else {
            editingUser.value = { name: '', email: '', role_id: 3, active: true }
            avatarPreview.value = null
        }
        
        selectedAvatarFile.value = null
        if (!modalInstance && modalElement.value) modalInstance = new Modal(modalElement.value)
        modalInstance?.show()
    }

    const saveUser = async () => {
        isSaving.value = true
        try {
            const formData = new FormData()
            formData.append('name', editingUser.value.name || '')
            formData.append('email', editingUser.value.email || '')
            formData.append('role_id', editingUser.value.role_id || 3)
            formData.append('active', editingUser.value.active ? 1 : 0)

            if (selectedAvatarFile.value) {
                formData.append('avatar', selectedAvatarFile.value)
            }

            if (editingUser.value.id) {
                formData.append('_method', 'PUT') 
                await UserService.update(editingUser.value.id, formData)
            } else {
                await UserService.create(formData)
            }

            modalInstance?.hide()
            await fetchUsers()
            Swal.fire({ icon: 'success', title: 'រក្សាទុកជោគជ័យ', timer: 1500, showConfirmButton: false, customClass: { popup: 'khmer-font' } })
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'កំហុស', text: 'សូមពិនិត្យទិន្នន័យម្តងទៀត', customClass: { popup: 'khmer-font' } })
        } finally {
            isSaving.value = false
        }
    }

    const confirmDelete = async (id) => {
        const result = await Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "គណនីនេះនឹងត្រូវលុប!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'យល់ព្រម',
            cancelButtonText: 'បោះបង់',
            customClass: { popup: 'khmer-font' }
        })

        if (result.isConfirmed) {
            await UserService.delete(id)
            fetchUsers()
        }
    }

    const handleAvatarChange = (e) => {
        const file = e.target.files[0]
        if (!file) return
        selectedAvatarFile.value = file
        avatarPreview.value = URL.createObjectURL(file) 
    }

    onMounted(fetchUsers)

    // Clean up memory
    onUnmounted(() => {
        if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
            URL.revokeObjectURL(avatarPreview.value)
        }
    })
</script>

