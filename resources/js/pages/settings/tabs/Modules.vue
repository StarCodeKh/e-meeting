<template>
    <div class="module-management">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="search-box position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    class="form-control ps-5 rounded-3 border-0 shadow-sm khmer-font" 
                    placeholder="ស្វែងរកម៉ូឌុល..."
                >
            </div>
            <button class="btn btn-primary rounded-3 px-4 shadow-sm khmer-font d-flex align-items-center" @click="openModal()">
                <i class="bi bi-plus-lg me-2"></i> បង្កើតថ្មី
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead class="table-light">
                        <tr class="khmer-font text-secondary small">
                            <th class="ps-4 border-0">ល.រ</th>
                            <th class="border-0">ម៉ូឌុល</th>
                            <th class="border-0">ការពិពណ៌នា</th> <th class="text-center border-0">លំដាប់</th>
                            <th class="text-center border-0">ស្ថានភាព</th>
                            <th class="text-end pe-4 border-0">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                <span class="khmer-font text-muted">កំពុងទាញទិន្នន័យ...</span>
                            </td>
                        </tr>

                        <tr v-for="(module, index) in filteredModules" :key="module.id" v-else-if="filteredModules.length > 0">
                            <td class="ps-4 fw-bold text-primary">{{ toKhmerNum(index + 1) }}</td>
                            <td>
                                <div class="khmer-font fw-medium text-dark">{{ module.label }}</div>
                                <code class="text-danger-emphasis bg-danger-subtle px-2 py-0 rounded-1 small border border-danger-subtle" style="font-size: 0.75rem;">
                                    {{ module.key_name }}
                                </code>
                            </td>
                            <td>
                                <div class="khmer-font text-muted small text-truncate" style="max-width: 250px;">
                                    {{ module.description || 'មិនមានការពិពណ៌នា' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-3 bg-light text-dark border">{{ toKhmerNum(module.sort_order) }}</span>
                            </td>
                            <td class="text-center">
                                <span v-if="module.is_active" class="badge rounded-3 text-success bg-success-subtle border border-success-subtle px-3 py-2">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> សកម្ម
                                </span>
                                <span v-else class="badge rounded-3 text-danger bg-danger-subtle border border-danger-subtle px-3 py-2">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> ផ្អាក
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button @click="openModal(module)" class="btn btn-outline-primary btn-sm border-light-subtle shadow-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button @click="handleDelete(module.id)" class="btn btn-outline-danger btn-sm border-light-subtle shadow-sm">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td colspan="6" class="text-center py-5 khmer-font text-muted">មិនមានទិន្នន័យឡើយ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title khmer-font fw-bold text-primary">
                            {{ isEdit ? 'កែប្រែម៉ូឌុល' : 'បង្កើតម៉ូឌុលថ្មី' }}
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="handleSubmit">
                        <div class="modal-body py-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small">ឈ្មោះកូដ (Key Name)</label>
                                    <input v-model="form.key_name" type="text" class="form-control rounded-3 border-light-subtle shadow-none px-3" placeholder="user_management" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small">ឈ្មោះបង្ហាញ (Label)</label>
                                    <input v-model="form.label" type="text" class="form-control rounded-3 border-light-subtle shadow-none px-3" placeholder="គ្រប់គ្រងអ្នកប្រើប្រាស់" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label khmer-font small">ការពិពណ៌នា (Description)</label>
                                    <textarea v-model="form.description" class="form-control rounded-3 border-light-subtle shadow-none px-3" rows="3" placeholder="ពន្យល់អំពីមុខងាររបស់ម៉ូឌុលនេះ..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label khmer-font small">លំដាប់ (Sort Order)</label>
                                    <input v-model="form.sort_order" type="number" class="form-control rounded-3 border-light-subtle shadow-none px-3">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch mb-2 ms-2">
                                        <input v-model="form.is_active" class="form-check-input ms-0 shadow-none" type="checkbox" id="activeSwitch">
                                        <label class="form-check-label khmer-font small ms-2" for="activeSwitch">ស្ថានភាពសកម្ម</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light khmer-font rounded-3 px-4" data-bs-dismiss="modal">បោះបង់</button>
                            <button type="submit" class="btn btn-primary khmer-font rounded-3 px-4 shadow-sm" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                {{ isEdit ? 'រក្សាទុកការកែប្រែ' : 'បង្កើតឥឡូវនេះ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ModuleService from '@/services/ModuleService'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'

// --- State Management ---
const modules = ref([])
const loading = ref(true)
const saving = ref(false)
const searchQuery = ref('')
const isEdit = ref(false)
const currentId = ref(null)

const initialForm = {
    key_name: '',
    label: '',
    sort_order: 0,
    is_active: true
}

const form = ref({ ...initialForm })
let modalInstance = null

// --- Lifecycle ---
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

// --- Methods ---
const resetForm = () => {
    isEdit.value = false
    currentId.value = null
    form.value = { ...initialForm }
}

const fetchModules = async () => {
    loading.value = true
    try {
        const res = await ModuleService.getAll()
        modules.value = res.data.data || res.data || []
    } catch (err) {
        console.error("Fetch Error:", err)
    } finally {
        loading.value = false
    }
}

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

const handleSubmit = async () => {
    saving.value = true
    try {
        const action = isEdit.value 
            ? ModuleService.update(currentId.value, form.value)
            : ModuleService.create(form.value)
            
        await action
        if (modalInstance) modalInstance.hide()
        
        toast('រក្សាទុកជោគជ័យ', 'success')
        fetchModules()
    } catch (err) {
        const errorMsg = err.response?.data?.message || 'មិនអាចរក្សាទុកបានទេ'
        Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg, customClass: { popup: 'khmer-font' } })
    } finally {
        saving.value = false
    }
}

const handleDelete = async (id) => {
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
            await ModuleService.delete(id)
            toast('លុបជោគជ័យ', 'success')
            fetchModules()
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: 'មិនអាចលុបបានទេ', customClass: { popup: 'khmer-font' } })
        }
    }
}

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

const filteredModules = computed(() => {
    const query = searchQuery.value.toLowerCase()
    return modules.value.filter(m => 
        m.label.toLowerCase().includes(query) || 
        m.key_name.toLowerCase().includes(query)
    )
})

const toKhmerNum = (n) => n?.toString().replace(/\d/g, d => ['០','១','២','៣','៤','៥','៦','៧','៨','៩'][d])
</script>

<style scoped>
.khmer-font { font-family: 'Kantumruy Pro', sans-serif; }
.search-box input { width: 300px; transition: width 0.3s ease; }
.search-box input:focus { width: 350px; }
.custom-table tbody tr { transition: background-color 0.2s; }
/* SweetAlert2 Khmer Font */
:deep(.khmer-font) { font-family: 'Kantumruy Pro', sans-serif !important; }
</style>