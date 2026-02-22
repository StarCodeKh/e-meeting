<template>
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm d-inline-flex border border-light">
        <li class="nav-item">
            <button class="nav-link khmer-font px-4 py-2 transition-all" 
                :class="{ active: activeTab === 'list' }" @click="switchTab('list')">
                <i class="bi bi-shield-lock me-2"></i>បញ្ជីតួនាទី
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link khmer-font px-4 py-2 transition-all" 
                :class="{ active: activeTab === 'form' }" @click="switchTab('form')">
                <i class="bi bi-plus-circle me-2"></i>{{ isEdit ? 'កែប្រែតួនាទី' : 'បង្កើតតួនាទីថ្មី' }}
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div v-if="activeTab === 'list'" class="animate__animated animate__fadeIn">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="khmer-font small text-muted text-uppercase">
                                <th class="ps-4 py-3">ឈ្មោះតួនាទី</th>
                                <th class="py-3">សិទ្ធិប្រើប្រាស់</th>
                                <th class="py-3 text-end pe-4">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="isLoading">
                                <td colspan="3" class="text-center py-5">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <div class="mt-2 small text-muted khmer-font">កំពុងដំណើរការ...</div>
                                </td>
                            </tr>
                            <tr v-for="role in roles" :key="role.id" class="cursor-pointer">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark fs-6">{{ role.name }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span v-for="p in role.permissions.slice(0, 5)" :key="p.id" 
                                            class="badge bg-primary-subtle text-primary border border-primary-subtle fw-normal rounded-3 px-2">
                                            {{ formatLabel(p.name) }}
                                        </span>
                                        <span v-if="role.permissions.length > 5" class="badge bg-light text-muted border fw-normal rounded-3">
                                            +{{ role.permissions.length - 5 }} ទៀត
                                        </span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button @click="editRole(role)" class="btn btn-sm btn-outline-primary border-0 rounded-3 me-1" title="កែប្រែ">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button @click="handleDelete(role.id)" class="btn btn-sm btn-outline-danger border-0 rounded-3" title="លុប">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-else class="animate__animated animate__fadeIn">
            <form @submit.prevent="handleSubmit" class="card border-0 shadow-sm rounded-4 p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label khmer-font fw-bold text-secondary">ឈ្មោះតួនាទី <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge"></i></span>
                            <input v-model="form.name" type="text" class="form-control border-start-0 ps-0 shadow-none" 
                                placeholder="ឧទាហរណ៍: Manager" required>
                        </div>
                    </div>
                </div>

                <div class="permissions-section mt-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="khmer-font fw-bold mb-0 text-secondary">កំណត់សិទ្ធិប្រើប្រាស់</h6>
                        <span class="badge bg-light text-dark border fw-normal">{{ form.permissions.length }} សិទ្ធិបានជ្រើសរើស</span>
                    </div>
                    
                    <div class="row g-4">
                        <div v-for="(perms, moduleKey) in groupedPermissions" :key="moduleKey" class="col-md-6 col-lg-4">
                            <div class="card border shadow-none h-100 rounded-3">
                                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center py-2 px-3">
                                    <span class="fw-bold text-primary small text-uppercase khmer-font">{{ translateModule(moduleKey) }}</span>
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold x-small" @click="toggleModule(perms)">
                                        {{ isAllModuleSelected(perms) ? 'ដកចេញ' : 'ជ្រើសរើសទាំងអស់' }}
                                    </button>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div v-for="perm in perms" :key="perm.id" class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" :id="'perm-'+perm.id" :value="perm.name" 
                                                    v-model="form.permissions" class="form-check-input cursor-pointer">
                                                <label :for="'perm-'+perm.id" class="form-check-label small cursor-pointer w-100">
                                                    {{ formatLabel(perm.name) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top d-flex gap-3 justify-content-center">
                    <button type="button" 
                            class="btn btn-light px-4 khmer-font rounded-3 border shadow-sm transition-all" 
                            @click="cancelEdit">
                        <i class="bi bi-arrow-left-circle me-2"></i>បោះបង់
                    </button>

                    <button type="submit" 
                            class="btn btn-primary px-5 khmer-font rounded-3 shadow-sm transition-all" 
                            :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check2-circle me-2"></i>
                        {{ saving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue'
    import { RoleService } from '@/services/RoleService'
    import Swal from 'sweetalert2'

    // States
    const activeTab = ref('list')
    const roles = ref([])
    const allPermissions = ref([])
    const saving = ref(false)
    const selectedId = ref(null)
    const form = ref({ name: '', permissions: [] })
    const isEdit = computed(() => !!selectedId.value)

    // Actions
    const switchTab = (tab) => {
        if(tab === 'form' && !isEdit.value) resetForm()
        activeTab.value = tab
    }

    const resetForm = () => {
        selectedId.value = null
        form.value = { name: '', permissions: [] }
    }

    const fetchInitialData = async () => {
        const [rRes, pRes] = await Promise.all([RoleService.getAll(), RoleService.getAllPermissions()])
        roles.value = rRes.data || rRes
        allPermissions.value = pRes.data || pRes
    }

    const editRole = (role) => {
        selectedId.value = role.id
        form.value = { 
            name: role.name, 
            permissions: role.permissions.map(p => p.name) 
        }
        activeTab.value = 'form'
    }

    const cancelEdit = () => {
        resetForm()
        activeTab.value = 'list'
    }

    const handleSubmit = async () => {
        if (form.value.permissions.length === 0) return toast('warning', 'សូមជ្រើសរើសសិទ្ធិ!')
        saving.value = true
        try {
            if (isEdit.value) await RoleService.update(selectedId.value, form.value)
            else await RoleService.create(form.value)
            
            toast('success', 'ជោគជ័យ!')
            await fetchInitialData()
            cancelEdit()
        } catch (e) { toast('error', e.message) }
        finally { saving.value = false }
    }

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "ការលុបនេះមិនអាចយកមកវិញបានឡើយ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'លុបចេញ!',
            cancelButtonText: 'បោះបង់',
            reverseButtons: true,
            customClass: {
                popup: 'khmer-font rounded-4',
                title: 'khmer-font',
                htmlContainer: 'khmer-font',
                confirmButton: 'btn btn-danger px-4 py-2 mx-2 khmer-font',
                cancelButton: 'btn btn-light px-4 py-2 mx-2 khmer-font'
            },
            buttonsStyling: false
        })

        if (result.isConfirmed) {
            Swal.fire({
                title: 'កំពុងលុប...',
                customClass: { title: 'khmer-font' },
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                await RoleService.delete(id)
                await fetchInitialData()
                
                Swal.close();

                Swal.fire({ 
                    icon: 'success', 
                    title: 'លុបតួនាទីរួចរាល់!', 
                    toast: true, 
                    position: 'top-end', 
                    showConfirmButton: false, 
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: { title: 'khmer-font' }
                });

            } catch (e) {
                Swal.close();

                const errorMsg = e.response?.status === 403 
                    ? 'អ្នកមិនមានសិទ្ធិលុបតួនាទីនេះទេ' 
                    : (e.message || 'មិនអាចលុបបានទេ!');

                Swal.fire({ 
                    icon: 'error', 
                    title: errorMsg, 
                    toast: true, 
                    position: 'top-end', 
                    showConfirmButton: false, 
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: { title: 'khmer-font' }
                });
            }
        }
    }

    // Grouping Logic (Fixed)
    const groupedPermissions = computed(() => {
        return allPermissions.value.reduce((groups, perm) => {
            const parts = perm.name.split('_')
            const actions = ['view', 'create', 'edit', 'delete', 'manage', 'update']
            let module = parts.find(p => !actions.includes(p)) || 'ផ្សេងៗ'
            if (!groups[module]) groups[module] = []
            groups[module].push(perm)
            return groups
        }, {})
    })

    // Helpers
    const formatLabel = (name) => {
    const trans = { 'view': 'មើល', 'list': 'បញ្ជី','create': 'បង្កើត', 'edit': 'កែប្រែ', 'delete': 'លុប', 'update': 'កែប្រែ', 'manage': 'គ្រប់គ្រង' }
    const found = Object.keys(trans).find(k => name.toLowerCase().includes(k))
        return found ? trans[found] : name
    }
    const translateModule = (m) => m.charAt(0).toUpperCase() + m.slice(1)
    const isAllModuleSelected = (perms) => perms.every(p => form.value.permissions.includes(p.name))
    const toggleModule = (perms) => {
    const names = perms.map(p => p.name)
    if (isAllModuleSelected(perms)) form.value.permissions = form.value.permissions.filter(n => !names.includes(n))
        else form.value.permissions = [...new Set([...form.value.permissions, ...names])]
    }
    const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })

    onMounted(fetchInitialData)
</script>

<style scoped>

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #ced4da;
        border-radius: 10px;
        border: 2px solid #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }

    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #ced4da #f1f1f1;
    }

    .transition-all {
        transition: all 0.3s ease; 
    }

    .nav-link.active { 
        background-color: var(--bs-primary) !important;
        color: white !important; 
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .x-small {
        font-size: 0.75rem;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .form-check-input:checked { 
        background-color: var(--bs-primary); 
        border-color: var(--bs-primary);
    }
</style>