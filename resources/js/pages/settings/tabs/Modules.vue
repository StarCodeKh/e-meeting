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

        <div class="border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead class="bg-light text-nowrap">
                        <tr class="khmer-font text-secondary small text-uppercase">
                            <th class="ps-4 border-0 py-3">ល.រ</th>
                            <th class="border-0 py-3">ព័ត៌មានម៉ូឌុល</th>
                            <!-- <th class="border-0 py-3">ការពិពណ៌នា</th>  -->
                            <!-- <th class="text-center border-0 py-3">លំដាប់</th> -->
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
                                <div class="khmer-font fw-bold text-dark mb-1">{{ module.label }}</div>
                                <span class="badge bg-secondary-subtle text-secondary fw-normal border border-secondary-subtle">
                                    <i class="bi bi-code-slash me-1"></i>{{ module.key_name }}
                                </span>
                            </td>
                            <!-- <td>
                                <div class="khmer-font text-muted small text-truncate" style="max-width: 250px;">
                                    {{ module.description || 'មិនមានការពិពណ៌នា' }}
                                </div>
                            </td> -->
                            <!-- <td class="text-center">
                                <span class="badge rounded-3 bg-light text-dark border px-3">{{ toKhmerNum(module.sort_order) }}</span>
                            </td> -->
                            <td class="text-center">
                                <div v-if="module.is_active" class="badge rounded-pill text-success bg-success-subtle border border-success-subtle px-3 py-2 fw-normal">
                                    សកម្ម
                                </div>
                                <div v-else class="badge rounded-pill text-danger bg-danger-subtle border border-danger-subtle px-3 py-2 fw-normal">
                                    ផ្អាក
                                </div>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <button @click="openModal(module)" class="btn btn-sm btn-light border rounded-3 me-1 text-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button @click="handleDelete(module.id)" class="btn btn-sm btn-light border rounded-3 text-danger">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>

                        <tr v-else>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-folder-x fs-1 text-muted opacity-25 d-block mb-3"></i>
                                <span class="khmer-font text-muted">មិនមានទិន្នន័យម៉ូឌុលឡើយ</span>
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
                                    <label class="form-label khmer-font small fw-bold">ឈ្មោះកូដ (Key Name)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-terminal"></i></span>
                                        <input v-model="form.key_name" type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="user_management" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small fw-bold">ឈ្មោះបង្ហាញ (Label)</label>
                                    <input v-model="form.label" type="text" class="form-control shadow-none px-3" placeholder="គ្រប់គ្រងអ្នកប្រើប្រាស់" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label khmer-font small fw-bold">ការពិពណ៌នា</label>
                                    <textarea v-model="form.description" class="form-control shadow-none px-3" rows="3" placeholder="ពន្យល់អំពីមុខងារ..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small fw-bold">លំដាប់លំដោយ</label>
                                    <input v-model="form.sort_order" type="number" class="form-control shadow-none px-3">
                                </div>
                                <div class="col-md-6 d-flex align-items-center pt-3">
                                    <div class="form-check form-switch custom-switch mt-2">
                                        <input v-model="form.is_active" class="form-check-input ms-0 shadow-none pointer-link" type="checkbox" id="activeSwitch">
                                        <label class="form-check-label khmer-font small ms-2 pointer-link" for="activeSwitch">ស្ថានភាពសកម្ម</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0 py-3">
                            <button type="button" class="btn btn-link text-muted khmer-font text-decoration-none px-4" data-bs-dismiss="modal">បោះបង់</button>
                            <button type="submit" class="btn btn-primary khmer-font rounded-3 px-5 shadow-sm" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                                {{ isEdit ? 'រក្សាទុក' : 'បង្កើតឥឡូវនេះ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
    import { ref, computed, onMounted, watch } from 'vue'
    import ModuleService from '@/services/ModuleService'
    import Swal from 'sweetalert2'
    import { Modal } from 'bootstrap'
    import _ from 'lodash'

    // --- ១. ការគ្រប់គ្រង State (State Management) ---
    const modules = ref([])
    const loading = ref(true)
    const saving = ref(false)
    const searchQuery = ref('')
    const isEdit = ref(false)
    const currentId = ref(null)

    // Meta សម្រាប់រក្សាទុកព័ត៌មាន Pagination ពី Backend
    const meta = ref({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 5
    })

    const initialForm = {
        key_name: '',
        label: '',
        description: '',
        sort_order: 0,
        is_active: true
    }

    const form = ref({ ...initialForm })
    let modalInstance = null

    // --- ២. Lifecycle Hooks ---
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

    // --- ៣. មុខងារទាញទិន្នន័យ (API Methods) ---

    // ទាញយកម៉ូឌុល (គាំទ្រ Search និង Pagination ពី Server)
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
            console.error("Fetch Error:", err)
            toast('មិនអាចទាញទិន្នន័យបានទេ', 'error')
        } finally {
            loading.value = false
        }
    }

    // មុខងារស្វែងរក (រង់ចាំ ៥០០ មីលីវិនាទី ទើបបាញ់ទៅ API ដើម្បីកាត់បន្ថយ Load)
    watch(searchQuery, _.debounce(() => {
        fetchModules(1)
    }, 500))

    // --- ៤. ការគ្រប់គ្រង Form (Create / Update / Delete) ---
    const openModal = (item = null) => {
        if (item) {
            isEdit.value = true
            currentId.value = item.id
            form.value = { ...item, is_active: !!item.is_active }
        } else {
            resetForm()
        }
        if (modalInstance) modalInstance.show()
    }

    const resetForm = () => {
        isEdit.value = false
        currentId.value = null
        form.value = { ...initialForm }
    }

    const handleSubmit = async () => {
        saving.value = true
        try {
            if (isEdit.value) {
                await ModuleService.update(currentId.value, form.value)
            } else {
                await ModuleService.create(form.value)
            }
            
            if (modalInstance) modalInstance.hide()
            toast('រក្សាទុកទិន្នន័យជោគជ័យ', 'success')
            fetchModules(meta.value.current_page)
        } catch (err) {
            Swal.fire({ 
                icon: 'error', 
                title: 'បរាជ័យ', 
                text: err.message || 'មានបញ្ហាក្នុងការរក្សាទុក',
                customClass: { popup: 'khmer-font' } 
            })
        } finally {
            saving.value = false
        }
    }

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'តើអ្នកប្រាកដទេ?',
            text: "អ្នកនឹងមិនអាចត្រឡប់ទិន្នន័យនេះមកវិញបានទេ!",
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
                await ModuleService.delete(id)
                toast('លុបទិន្នន័យជោគជ័យ', 'success')
                const targetPage = modules.value.length === 1 && meta.value.current_page > 1 
                    ? meta.value.current_page - 1 
                    : meta.value.current_page
                fetchModules(targetPage)
            } catch (err) {
                toast(err.message || 'មិនអាចលុបបានទេ', 'error')
            }
        }
    }

    // --- ៥. មុខងារជំនួយ (Helpers) ---

    const toast = (title, icon) => {
        Swal.fire({
            icon,
            title,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            customClass: { popup: 'khmer-font' }
        })
    }

    const toKhmerNum = (n) => {
        if (n === null || n === undefined) return '០'
        return n.toString().replace(/\d/g, d => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d])
    }
    const activeGradient = 'linear-gradient(45deg, #3498db, #2980b9)';
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