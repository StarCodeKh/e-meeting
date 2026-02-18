<template>
    <div class="module-management animate__animated animate__fadeIn">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="search-box position-relative w-100" style="max-width: 350px;">
                <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input v-model="searchQuery" type="text" class="form-control ps-5 rounded-3 border-0 shadow-sm khmer-font py-2" placeholder="ស្វែងរកម៉ូឌុល...">
            </div>
            <button class="btn btn-primary rounded-3 px-4 shadow-sm khmer-font d-flex align-items-center py-2" @click="openModal()">
                <i class="bi bi-plus-lg me-2"></i> បង្កើតម៉ូឌុលថ្មី
            </button>
        </div>

        <div class="border-0 shadow-sm rounded-3 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead class="bg-light text-nowrap">
                        <tr class="khmer-font text-secondary small text-uppercase">
                            <th class="ps-4 border-0 py-3">ល.រ</th>
                            <th class="border-0 py-3">ព័ត៌មានម៉ូឌុល</th>
                            <th class="border-0 py-3">សិទ្ធិ (Permission)</th> 
                            <th class="text-center border-0 py-3">លំដាប់</th>
                            <th class="text-center border-0 py-3">ស្ថានភាព</th>
                            <th class="text-end pe-4 border-0 py-3">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div class="khmer-font text-muted small">កំពុងទាញទិន្នន័យ...</div>
                            </td>
                        </tr>

                        <tr v-for="(module, index) in modules" :key="module.id" v-else-if="modules.length > 0" class="transition-all">
                            <td class="ps-4 fw-bold text-muted">
                                {{ toKhmerNum((meta.current_page - 1) * meta.per_page + (index + 1)) }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box rounded-3 bg-primary-light text-primary me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i :class="['bi', module.icon || 'bi-grid-fill']" class="fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="khmer-font fw-bold text-dark mb-0">
                                            {{ module.label }}
                                            <i v-if="module.external" class="bi bi-box-arrow-up-right ms-1 small text-info" title="តំណភ្ជាប់ក្រៅ"></i>
                                        </div>
                                        <code class="extra-small text-primary">{{ module.path }}</code>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-secondary-subtle text-secondary fw-normal border border-secondary-subtle mb-1">
                                    <i class="bi bi-shield-lock me-1"></i>{{ module.permission_name || 'Public' }}
                                </span>
                                <div class="khmer-font text-muted extra-small text-truncate" style="max-width: 200px;">
                                    {{ module.description || 'មិនមានការពិពណ៌នា' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-3 bg-light text-dark border px-3">{{ toKhmerNum(module.sort_order) }}</span>
                            </td>
                            <td class="text-center">
                                <div :class="module.is_active ? 'badge text-success bg-success-subtle border-success-subtle' : 'badge text-danger bg-danger-subtle border-danger-subtle'" class="rounded-3 border px-3 py-2 fw-normal">
                                    {{ module.is_active ? 'សកម្ម' : 'ផ្អាក' }}
                                </div>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <button @click="openModal(module)" class="btn btn-sm btn-light border rounded-3 me-1 text-primary shadow-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button @click="handleDelete(module.id)" class="btn btn-sm btn-light border rounded-3 text-danger shadow-sm">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.last_page > 1" class="card-footer bg-white border-top-0 py-4">
                <nav class="d-flex flex-column align-items-center gap-3">
                    <div class="khmer-font text-muted small">
                        ទំព័រទី {{ toKhmerNum(meta.current_page) }} នៃ {{ toKhmerNum(meta.last_page) }} (សរុប៖ {{ toKhmerNum(meta.total) }} ម៉ូឌុល)
                    </div>

                    <ul class="pagination gap-2 mb-0">
                        <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                            <button class="page-link rounded-3 border-0 shadow-sm transition-all" @click="fetchModules(meta.current_page - 1)">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                        </li>

                        <li v-for="page in meta.last_page" :key="page" class="page-item d-none d-md-block" :class="{ active: meta.current_page === page }">
                            <button class="page-link rounded-3 border-0 shadow-sm khmer-font transition-all" 
                                :style="meta.current_page === page ? { background: activeGradient, color: 'white' } : {}" 
                                @click="fetchModules(page)">
                                {{ toKhmerNum(page) }}
                            </button>
                        </li>

                        <li class="page-item d-md-none active">
                            <button class="page-link rounded-3 border-0 shadow-sm khmer-font" :style="{ background: activeGradient, color: 'white' }">
                                {{ toKhmerNum(meta.current_page) }}
                            </button>
                        </li>

                        <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                            <button class="page-link rounded-3 border-0 shadow-sm transition-all" @click="fetchModules(meta.current_page + 1)">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary text-white border-0 py-3">
                        <h5 class="modal-title khmer-font fw-bold">
                            <i class="bi bi-grid-1x2-fill me-2"></i>
                            {{ isEdit ? 'កែប្រែព័ត៌មានម៉ូឌុល' : 'បង្កើតម៉ូឌុលថ្មី' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="handleSubmit">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small fw-bold">ឈ្មោះបង្ហាញ (Label)</label>
                                    <input v-model="form.label" type="text" class="form-control shadow-none px-3 py-2" placeholder="គ្រប់គ្រងអ្នកប្រើប្រាស់" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small fw-bold">ឈ្មោះកូដ (Key Name)</label>
                                    <input v-model="form.key_name" type="text" class="form-control shadow-none px-3 py-2" placeholder="user_management" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small fw-bold text-primary">ផ្លូវ Route (Path)</label>
                                    <input v-model="form.path" type="text" class="form-control shadow-none px-3 py-2" placeholder="/users" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small fw-bold text-primary">រូបតំណាង (Icon Class)</label>
                                    <input v-model="form.icon" type="text" class="form-control shadow-none px-3 py-2" placeholder="bi-people-fill">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label khmer-font small fw-bold text-danger">សិទ្ធិប្រើប្រាស់ (Permission Name)</label>
                                    <input v-model="form.permission_name" type="text" class="form-control shadow-none px-3 py-2" placeholder="view_users">
                                </div>
                                <div class="col-12">
                                    <label class="form-label khmer-font small fw-bold">ការពិពណ៌នា</label>
                                    <textarea v-model="form.description" class="form-control shadow-none px-3" rows="2"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label khmer-font small fw-bold">លំដាប់លំដោយ</label>
                                    <input v-model="form.sort_order" type="number" class="form-control shadow-none px-3 py-2">
                                </div>
                                <div class="col-md-8 d-flex align-items-center justify-content-around pt-4">
                                    <div class="form-check form-switch">
                                        <input v-model="form.external" class="form-check-input" type="checkbox" id="externalSwitch">
                                        <label class="form-check-label khmer-font small ms-2 fw-bold" for="externalSwitch">តំណភ្ជាប់ក្រៅ</label>
                                    </div>
                                    <div class="form-check form-switch custom-switch">
                                        <input v-model="form.is_active" class="form-check-input" type="checkbox" id="activeSwitch">
                                        <label class="form-check-label khmer-font small ms-2 fw-bold" for="activeSwitch">ស្ថានភាពសកម្ម</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0 py-2 d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-link text-muted khmer-font text-decoration-none px-4 transition-all" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>បោះបង់
                            </button>

                            <button type="submit" class="btn btn-primary khmer-font rounded-3 px-5 py-2 shadow-sm" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                <i v-else :class="isEdit ? 'bi bi-check-circle' : 'bi bi-plus-circle'" class="me-2"></i>
                                {{ saving ? 'កំពុងដំណើរការ...' : (isEdit ? 'រក្សាទុកការកែប្រែ' : 'បង្កើតឥឡូវនេះ') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, watch } from 'vue'
    import ModuleService from '@/services/ModuleService'
    import Swal from 'sweetalert2'
    import { Modal } from 'bootstrap'
    import _ from 'lodash'

    // --- State ---
    const modules = ref([])
    const loading = ref(true)
    const saving = ref(false)
    const searchQuery = ref('')
    const isEdit = ref(false)
    const currentId = ref(null)
    const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 5 })

    const initialForm = {
        key_name: '',
        label: '',
        path: '/',
        icon: 'bi-grid-fill',
        permission_name: '',
        description: '',
        sort_order: 0,
        external: false,
        is_active: true
    }

    const form = ref({ ...initialForm })
    let modalInstance = null

    onMounted(() => {
        fetchModules()
        const modalElement = document.getElementById('moduleModal')
        if (modalElement) {
            modalInstance = new Modal(modalElement)
            modalElement.addEventListener('hidden.bs.modal', () => {
                resetForm()
            })
        }
    })

    // --- Actions ---
    const fetchModules = async (page = 1) => {
        loading.value = true
        try {
            const res = await ModuleService.getAll({
                page: page,
                search: searchQuery.value,
                per_page: 5
            })
            modules.value = res.data || []
            meta.value = {
                current_page: res.current_page || 1,
                last_page: res.last_page || 1,
                total: res.total || 0,
                per_page: res.per_page || 5
            }
        } catch (err) {
            toast('មិនអាចទាញទិន្នន័យបានទេ', 'error')
        } finally {
            loading.value = false
        }
    }

    watch(searchQuery, _.debounce(() => fetchModules(1), 500))

    const openModal = (item = null) => {
        if (item) {
            isEdit.value = true
            currentId.value = item.id
            form.value = JSON.parse(JSON.stringify({
                ...item,
                is_active: item.is_active == 1 || item.is_active === true,
                external: item.external == 1 || item.external === true
            }))
        } else {
            resetForm()
        }
        modalInstance.show()
    }

    const resetForm = () => {
        isEdit.value = false
        currentId.value = null
        form.value = { ...initialForm }
    }

    const handleSubmit = async () => {
        if (saving.value) return
        saving.value = true
        
        try {
            // រៀបចំ Payload ឱ្យស្អាត (Normalization)
            const payload = {
                label: form.value.label,
                key_name: form.value.key_name,
                path: form.value.path,
                icon: form.value.icon,
                permission_name: form.value.permission_name,
                description: form.value.description,
                sort_order: parseInt(form.value.sort_order) || 0,
                is_active: form.value.is_active ? 1 : 0,
                external: form.value.external ? 1 : 0
            }

            if (isEdit.value) {
                await ModuleService.update(currentId.value, payload)
                toast('កែប្រែទិន្នន័យជោគជ័យ', 'success')
            } else {
                await ModuleService.create(payload)
                toast('បង្កើតថ្មីជោគជ័យ', 'success')
            }
            
            modalInstance.hide()
            fetchModules(isEdit.value ? meta.value.current_page : 1)
        } catch (err) {
            console.error(err)
            const errorMsg = err.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក';
            Swal.fire({ 
                icon: 'error', 
                title: 'បរាជ័យ', 
                text: errorMsg, 
                customClass: { popup: 'khmer-font' } 
            })
        } finally {
            saving.value = false
        }
    }

    // --- Helpers (Delete & Toast នៅដដែល) ---
    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'យល់ព្រមលុប',
            cancelButtonText: 'បោះបង់',
            customClass: { popup: 'khmer-font' }
        })

        if (result.isConfirmed) {
            try {
                await ModuleService.delete(id)
                toast('លុបទិន្នន័យជោគជ័យ', 'success')
                fetchModules(meta.value.current_page)
            } catch (err) {
                toast('មិនអាចលុបបានទេ', 'error')
            }
        }
    }

    const toast = (title, icon) => {
        Swal.fire({ icon, title, timer: 1500, showConfirmButton: false, toast: true, position: 'top-end', customClass: { popup: 'khmer-font' } })
    }

    const toKhmerNum = (n) => n?.toString().replace(/\d/g, d => '០១២៣៤៥៦៧៨៩'[d]) || '០'
</script>

<style scoped>
    .khmer-font { font-family: 'Kantumruy Pro', sans-serif; }
    .transition-all { transition: all 0.25s ease-in-out; }
    .transition-all:hover { background-color: rgba(var(--bs-primary-rgb), 0.03); }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .pointer-link { cursor: pointer; }
    .custom-table thead th { font-weight: 600; letter-spacing: 0.5px; }
    .form-control:focus { border-color: var(--bs-primary); background-color: #fcfcfc; }
    .modal-header .btn-close { font-size: 0.8rem; }
</style>