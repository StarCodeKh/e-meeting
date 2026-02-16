<template>
    <form @submit.prevent="handleUpdate">
        <div class="row g-3 p-2">
            <div class="col-md-6">
                <label class="form-label khmer-font small fw-bold text-secondary">ឈ្មោះពេញ</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-person"></i>
                    </span>
                    <input 
                        v-model="form.name"
                        type="text" 
                        class="form-control border-start-0 ps-0 shadow-none khmer-font" 
                        :class="{ 'is-invalid': errors.name }"
                        placeholder="បញ្ចូលឈ្មោះពេញ..."
                    >
                    <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label khmer-font small fw-bold text-secondary">អុីមែល</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input 
                        v-model="form.email"
                        type="email" 
                        class="form-control border-start-0 ps-0 shadow-none khmer-font" 
                        :class="{ 'is-invalid': errors.email }"
                        placeholder="example@mail.com"
                    >
                    <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                </div>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary rounded-3 px-5 fw-bold khmer-font shadow-sm py-2" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="bi bi-check-circle me-2"></i>
                    {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការផ្លាស់ប្តូរ' }}
                </button>
            </div>
        </div>
    </form>
</template>

<script setup>
    import { reactive, ref, onMounted } from 'vue';
    import axios from 'axios';
    import Swal from 'sweetalert2';

    // ១. កំណត់ State សម្រាប់ទិន្នន័យ
    const form = reactive({
        name: '',
        email: ''
    });

    const errors = ref({});
    const loading = ref(false);

    // ២. ទាញយកទិន្នន័យបច្ចុប្បន្នមកបង្ហាញ (ឧទាហរណ៍៖ Profile User)
    const fetchProfile = async () => {
        try {
            const res = await axios.get('/api/user/profile');
            form.name = res.data.name;
            form.email = res.data.email;
        } catch (e) {
            console.error("Fetch Error:", e);
        }
    };

    // ៣. មុខងាររក្សាទុក
    const handleUpdate = async () => {
        loading.value = true;
        errors.value = {};
        
        try {
            await axios.put('/api/user/profile', form);
            
            Swal.fire({
                icon: 'success',
                title: 'ជោគជ័យ',
                text: 'ព័ត៌មានរបស់អ្នកត្រូវបានធ្វើបច្ចុប្បន្នភាព',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } catch (err) {
            if (err.response?.status === 422) {
                errors.value = err.response.data.errors;
            } else {
                Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: 'មិនអាចរក្សាទុកបានទេ!' });
            }
        } finally {
            loading.value = false;
        }
    };

    onMounted(fetchProfile);
</script>


<style scoped>
    .input-group-text {
        border-color: #dee2e6;
    }
    .form-control:focus {
        border-color: #dee2e6;
        background-color: #fcfcfc;
    }
</style>