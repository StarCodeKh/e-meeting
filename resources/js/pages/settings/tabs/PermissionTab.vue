<template>
    <div class="animate__animated animate__fadeIn">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-md-top" style="top: 1rem; z-index: 10;">
                    <h6 class="khmer-font fw-bold mb-3 text-primary">
                        <i class="bi bi-shield-plus me-2"></i>បង្កើតសិទ្ធិថ្មី
                    </h6>
                    <form @submit.prevent="handleSave">
                        <div class="mb-3">
                            <label class="small text-muted mb-2 khmer-font">ឈ្មោះសិទ្ធិ (Permission Name)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input v-model="form.name" type="text" 
                                    class="form-control rounded-end-3 border-start-0 ps-0 shadow-none" 
                                    placeholder="ឧទាហរណ៍: user_view" required>
                            </div>
                            <div class="form-text x-small text-muted mt-2">
                                <i class="bi bi-info-circle me-1"></i>ប្រព័ន្ធនឹងប្តូរឈ្មោះទៅជា "lowercase_with_underscore"
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-3 khmer-font py-2 shadow-sm" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-plus-circle me-1"></i> រក្សាទុកសិទ្ធិ
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                        <div class="khmer-font fw-bold text-dark">
                            <i class="bi bi-list-check me-2 text-primary"></i>បញ្ជីសិទ្ធិក្នុងប្រព័ន្ធ
                        </div>
                        <div class="position-relative w-50">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"></i>
                            <input v-model="search" type="text" 
                                class="form-control form-control-sm ps-4 rounded-3 border-light-subtle shadow-none" 
                                placeholder="ស្វែងរកឈ្មោះសិទ្ធិ...">
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 550px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top" style="z-index: 1;">
                                <tr class="small text-muted khmer-font text-uppercase">
                                    <th class="ps-4 py-3">ឈ្មោះសិទ្ធិ</th>
                                    <th class="py-3">Guard</th>
                                    <th class="text-end pe-4 py-3">សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="loadingData">
                                    <td colspan="3" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                        <div class="mt-2 small text-muted">កំពុងទាញទិន្នន័យ...</div>
                                    </td>
                                </tr>

                                <tr v-for="perm in filteredPermissions" :key="perm.id" class="transition-all">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-2 p-1 me-2" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-lock-fill small"></i>
                                            </div>
                                            <code class="text-dark fw-bold">{{ perm.name }}</code>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border fw-normal rounded-3 px-2">
                                            {{ perm.guard_name }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button @click="confirmDelete(perm.id)" 
                                            class="btn btn-sm btn-outline-danger border-0 rounded-3" 
                                            title="លុបចេញ">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="!loadingData && filteredPermissions.length === 0">
                                    <td colspan="3" class="text-center py-5">
                                        <i class="bi bi-shield-exclamation fs-2 text-muted mb-2 d-block"></i>
                                        <span class="text-muted khmer-font">មិនមានទិន្នន័យឡើយ</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <small class="text-muted khmer-font">សរុប៖ {{ filteredPermissions.length }} សិទ្ធិ</small>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { PermissionService } from '@/services/PermissionService';
    import Swal from 'sweetalert2';

    // --- States ---
    const permissions = ref([]);
    const loading = ref(false);
    const loadingData = ref(false);
    const search = ref('');
    const form = ref({ name: '' });

    // --- ១. ទាញទិន្នន័យ (Fetch Data) ---
    const fetchPermissions = async () => {
        loadingData.value = true;
        try {
            const res = await PermissionService.getAll(1, search.value);
            
            if (res && res.status === 'success') {
                permissions.value = res.data; 
            } else {
                permissions.value = res.data || res || [];
            }
        } catch (e) {
            console.error("Fetch Error:", e);
            permissions.value = [];
        } finally {
            loadingData.value = false;
        }
    };

    // --- ២. រក្សាទុក (Save Data) ---
    const handleSave = async () => {
        loading.value = true;
        try {
            await PermissionService.create({ name: form.value.name });
            
            form.value.name = '';
            await fetchPermissions();
            
            toast('success', 'រក្សាទុកជោគជ័យ');
        } catch (e) {
            Swal.fire({ 
                icon: 'error', 
                title: 'បរាជ័យ', 
                text: e.message || 'ឈ្មោះនេះមានរួចហើយ!' 
            });
        } finally { 
            loading.value = false; 
        }
    };

    // --- ៣. លុប (Delete Data) ---
    const confirmDelete = async (id) => {
        const result = await Swal.fire({ 
            title: 'លុបសិទ្ធិនេះ?', 
            text: "តើអ្នកប្រាកដជាចង់លុបសិទ្ធិនេះមែនទេ?",
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonText: 'បាទ លុបចេញ',
            cancelButtonText: 'បោះបង់'
        });

        if (result.isConfirmed) {
            try {
                await PermissionService.delete(id);
                await fetchPermissions();
                toast('success', 'លុបរួចរាល់');
            } catch (e) {
                toast('error', e.message || 'មិនអាចលុបបានទេ');
            }
        }
    };

    // --- Helpers ---
    const toast = (icon, title) => {
        Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
    };

    const filteredPermissions = computed(() => {
        const list = Array.isArray(permissions.value) ? permissions.value : [];
        if (!search.value) return list;

        return list.filter(p => 
            p.name && p.name.toLowerCase().includes(search.value.toLowerCase())
        );
    });

    onMounted(fetchPermissions);
</script>

<style scoped>
    .transition-all { transition: all 0.2s ease-in-out; }
    .transition-all:hover { background-color: rgba(0, 123, 255, 0.02); }
    .x-small { font-size: 0.72rem; }
    code { font-size: 0.85rem; letter-spacing: 0.5px; }
    .input-group-text { font-size: 0.9rem; }
</style>