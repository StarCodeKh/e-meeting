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
                            <input v-model="searchQuery" type="text" class="form-control khmer-font py-2 ps-5 border-0 shadow-none bg-light rounded-3" placeholder="ស្វែងរកតាមឈ្មោះ ឬអុីមែល...">
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
                    <div class="card border-0 shadow-sm rounded-4 p-4 animate-pulse">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-secondary-subtle rounded-circle" style="width: 55px; height: 55px;"></div>
                            <div class="flex-grow-1">
                                <div class="bg-secondary-subtle rounded-pill mb-2" style="width: 60%; height: 12px;"></div>
                                <div class="bg-secondary-subtle rounded-pill" style="width: 40%; height: 10px;"></div>
                            </div>
                        </div>
                        <div class="bg-secondary-subtle rounded-pill w-100 mb-2" style="height: 10px;"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="users.length === 0" class="text-center py-5">
                <div class="py-5 bg-white rounded-4 shadow-sm border">
                    <i class="bi bi-people text-muted opacity-25" style="font-size: 5rem;"></i>
                    <h5 class="khmer-font text-muted mt-3">មិនមានទិន្នន័យអ្នកប្រើប្រាស់ឡើយ</h5>
                </div>
            </div>

            <div v-else class="row">
                <div v-for="user in users" :key="user.id" class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 user-card border-top border-3 transition-all" :class="getRoleBorder(user.role)">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'" class="rounded-circle border border-2 shadow-sm object-fit-cover" width="55" height="55">
                                        <span class="position-absolute bottom-0 end-0 border border-white border-2 rounded-circle shadow-sm" :style="{ width: '14px', height: '14px', backgroundColor: user.status === 'active' ? '#10b981' : '#94a3b8' }"></span>
                                    </div>
                                    <div class="text-truncate">
                                        <h6 class="khmer-font fw-bold mb-0 text-dark text-truncate" style="max-width: 150px;">{{ user.name }}</h6>
                                        <span class="extra-small text-muted">{{ user.email }}</span>
                                    </div>
                                </div>
                                
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg khmer-font p-2">
                                        <li>
                                            <button class="dropdown-item py-2 rounded-2 text-primary" @click="openEditModal(user)">
                                            <i class="bi bi-pencil-square me-2"></i> កែសម្រួល</button>
                                        </li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li>
                                            <button class="dropdown-item py-2 rounded-2 text-danger" @click="confirmDelete(user.id)">
                                            <i class="bi bi-trash3 me-2"></i> លុបចេញ</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge rounded-pill px-3 py-1 khmer-font fw-normal" :class="getRoleBadgeClass(user.role)">
                                    {{ user.role?.toUpperCase() || 'USER' }}
                                </span>
                            </div>

                            <div class="mt-auto pt-3 border-top border-light-subtle">
                                <div class="d-flex justify-content-between align-items-center small text-muted khmer-font">
                                    <span><i class="bi bi-calendar-check me-1 text-primary"></i> ចូលរួម៖ <span class="text-dark fw-bold">{{ toKhmerNum(user.joined_date || 'N/A') }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4 mb-5">
                <nav>
                    <ul class="pagination pagination-sm gap-2">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <button class="page-link rounded-3 border-0 shadow-sm" @click="pagination.current_page--"><i class="bi bi-chevron-left"></i></button>
                        </li>
                        <li v-for="page in pagination.last_page" :key="page" class="page-item">
                            <button class="page-link rounded-3 border-0 shadow-sm khmer-font" 
                                    :class="{ 'bg-primary text-white fw-bold': pagination.current_page === page }"
                                    @click="pagination.current_page = page">
                                {{ toKhmerNum(page) }}
                            </button>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <button class="page-link rounded-3 border-0 shadow-sm" @click="pagination.current_page++"><i class="bi bi-chevron-right"></i></button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="modal fade" id="editUserModal" ref="modalElement" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 bg-primary p-4 text-white">
                        <h5 class="modal-title khmer-font fw-bold">
                            {{ editingUser.id ? 'កែសម្រួលព័ត៌មាន' : 'បន្ថែមសមាជិកថ្មី' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img :src="avatarPreview || editingUser.avatar_url || 'https://ui-avatars.com/api/?name=User&background=eee'" class="rounded-circle border border-4 border-white shadow object-fit-cover" width="110" height="110">
                                <label for="avatarInput" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle border border-white border-3">
                                    <i class="bi bi-camera"></i>
                                    <input type="file" id="avatarInput" class="d-none" @change="handleAvatarChange" accept="image/*">
                                </label>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">ឈ្មោះពេញ</label>
                                <input v-model="editingUser.name" type="text" class="form-control bg-light border-0" :class="{'is-invalid': serverErrors.name}">
                                <div class="invalid-feedback khmer-font small">{{ serverErrors.name?.[0] }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">អុីមែល</label>
                                <input v-model="editingUser.email" type="email" class="form-control bg-light border-0" :class="{'is-invalid': serverErrors.email}">
                                <div class="invalid-feedback khmer-font small">{{ serverErrors.email?.[0] }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="khmer-font small fw-bold text-dark mb-1">លេខសម្ងាត់ {{ editingUser.id ? '(ទុកទទេបើមិនចង់ប្តូរ)' : '' }}</label>
                                <input v-model="editingUser.password" type="password" class="form-control bg-light border-0" :class="{'is-invalid': serverErrors.password}">
                                <div class="invalid-feedback khmer-font small">{{ serverErrors.password?.[0] }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">តួនាទី</label>
                                <select v-model="editingUser.role" class="form-select khmer-font bg-light border-0" :class="{'is-invalid': serverErrors.role}">
                                    <option value="" disabled>ជ្រើសរើសតួនាទី</option>
                                    <option v-for="role in rolesList" :key="role.id" :value="role.name">
                                        {{ role.name.toUpperCase() }}
                                    </option>
                                </select>
                                <div class="invalid-feedback khmer-font small">{{ serverErrors.role?.[0] }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="khmer-font small fw-bold text-dark mb-1">ស្ថានភាព</label>
                                <div class="p-2 bg-light rounded-3 d-flex align-items-center justify-content-between border">
                                    <label class="khmer-font extra-small text-muted">{{ editingUser.is_active ? 'ដំណើរការ' : 'ផ្អាក' }}</label>
                                    <div class="form-check form-switch m-0">
                                        <input v-model="editingUser.is_active" class="form-check-input" type="checkbox">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-0">
                        <button class="btn btn-light khmer-font px-4 rounded-3" data-bs-dismiss="modal">បោះបង់</button>
                        <button class="btn btn-primary khmer-font px-5 rounded-3 shadow-sm" :disabled="isSaving" @click="saveUser">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                            រក្សាទុកទិន្នន័យ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import Swal from 'sweetalert2'
    import { Modal } from 'bootstrap'
    import { ref, watch, onMounted, onUnmounted } from 'vue'
    import { UserService } from '@/services/UserService'
    import { RoleService } from '@/services/RoleService' // បន្ថែមការ Import
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'

    // --- 1. States ---
    const users = ref([])
    const rolesList = ref([]) // State សម្រាប់ទាញ Role ពី API
    const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
    const isLoading = ref(false)
    const isSaving = ref(false)
    const searchQuery = ref('')
    const serverErrors = ref({})
    const editingUser = ref({})
    const avatarPreview = ref(null)
    const selectedAvatarFile = ref(null)
    const modalElement = ref(null)
    let modalInstance = null

    // ពណ៌សម្រាប់ Role (Static Config for UI)
    const ROLE_UI = {
        'admin': { badge: 'bg-success text-white', border: 'border-success' },
        'staff': { badge: 'bg-warning-subtle text-warning', border: 'border-warning' },
        'user': { badge: 'bg-info-subtle text-info', border: 'border-info' }
    }

    // --- 2. Helpers ---
    const toKhmerNum = (n) => n?.toString().replace(/\d/g, d => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d])
    const getRoleBadgeClass = (role) => ROLE_UI[String(role).toLowerCase()]?.badge || 'bg-light text-muted'
    const getRoleBorder = (role) => ROLE_UI[String(role).toLowerCase()]?.border || 'border-light'

    const alertPop = (title, icon = 'success', text = '') => {
        Swal.fire({
            title, text, icon,
            confirmButtonText: 'យល់ព្រម',
            customClass: { popup: 'khmer-font', confirmButton: 'btn btn-primary px-4' },
            buttonsStyling: false
        })
    }

    // --- 3. CRUD & Logic ---
    const fetchUsers = async () => {
        isLoading.value = true;
        try {
            const response = await UserService.getAll(pagination.value.current_page, searchQuery.value);
            users.value = response.data;
            pagination.value = response.meta || response;
        } catch (e) {
            console.error(e);
        } finally { isLoading.value = false; }
    };

    const fetchRoles = async () => {
        try {
            const response = await RoleService.getRolesForSelect();
            rolesList.value = response;
        } catch (e) { console.error("Error loading roles", e); }
    }

    const openEditModal = (user = null) => {
        serverErrors.value = {};
        selectedAvatarFile.value = null;
        avatarPreview.value = null;

        if (user) {
            editingUser.value = { ...user };
            editingUser.value.role = String(user.role).toLowerCase(); 
            editingUser.value.is_active = (user.status === 'active');
            editingUser.value.password = '';
            avatarPreview.value = user.avatar_url;
        } else {
            editingUser.value = { name: '', email: '', role: 'user', is_active: true, password: '' };
        }
        
        if (!modalInstance && modalElement.value) modalInstance = new Modal(modalElement.value);
        modalInstance?.show();
    };

    const handleAvatarChange = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        selectedAvatarFile.value = file;
        avatarPreview.value = URL.createObjectURL(file);
    };

    const saveUser = async () => {
        if (isSaving.value) return;
        isSaving.value = true;
        serverErrors.value = {};

        try {
            const formData = new FormData();
            formData.append('name', editingUser.value.name || '');
            formData.append('email', editingUser.value.email || '');
            
            // --- កែសម្រួលចំណុចនេះ ---
            // ទាញយកតម្លៃ Role ចេញមក (ទោះជា Array ឬ String)
            const roleValue = Array.isArray(editingUser.value.role) 
                ? editingUser.value.role[0] 
                : editingUser.value.role;

            // ប្រើ 'role[]' ជា Key ដើម្បីឱ្យ Laravel Validation ចាត់ទុកវាជា Array
            if (roleValue) {
                formData.append('role[]', roleValue); 
            } else {
                formData.append('role[]', 'user'); // តម្លៃ default បើអត់មានរើស
            }
            // -----------------------

            formData.append('status', editingUser.value.is_active ? 'active' : 'inactive');

            if (selectedAvatarFile.value) formData.append('avatar', selectedAvatarFile.value);
            if (editingUser.value.password) formData.append('password', editingUser.value.password);

            if (editingUser.value.id) {
                // ចំណុចសំខាន់សម្រាប់ Update ជាមួយ File ក្នុង Laravel
                formData.append('_method', 'PUT'); 
                await UserService.update(editingUser.value.id, formData);
            } else {
                await UserService.create(formData);
            }

            modalInstance?.hide();
            alertPop('រក្សាទុកជោគជ័យ!');
            fetchUsers();
        } catch (error) {
            if (error.response?.status === 422) {
                serverErrors.value = error.response.data.errors;
                // បន្ថែមការ Scroll ទៅរកកន្លែង Error បើចង់
            } else {
                alertPop('បរាជ័យ!', 'error', error.response?.data?.message || error.message);
            }
        } finally { 
            isSaving.value = false; 
        }
    };
    
    const confirmDelete = async (id) => {
        const result = await Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?', text: "ទិន្នន័យនឹងត្រូវលុប!", icon: 'warning',
            showCancelButton: true, confirmButtonText: 'យល់ព្រម',
            customClass: { popup: 'khmer-font', confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-light' },
            buttonsStyling: false
        });
        if (result.isConfirmed) {
            try {
                await UserService.delete(id);
                alertPop('លុបជោគជ័យ!');
                fetchUsers();
            } catch (e) { alertPop('កំហុស!', 'error'); }
        }
    }

    // --- 4. Watchers & Lifecycle ---
    watch(() => pagination.value.current_page, fetchUsers);
    watch(searchQuery, () => {
        setTimeout(() => { pagination.value.current_page = 1; fetchUsers(); }, 500);
    });

    onMounted(() => {
        fetchUsers();
        fetchRoles();
    });

    onUnmounted(() => {
        if (avatarPreview.value?.startsWith('blob:')) URL.revokeObjectURL(avatarPreview.value);
        modalInstance?.dispose();
    });
</script>