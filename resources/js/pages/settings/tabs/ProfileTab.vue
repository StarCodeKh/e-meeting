<template>
    <div class="profile-container khmer-font">
        <form @submit.prevent="handleUpdate">
            <div class="row g-4">
                <div class="col-12 text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <img v-if="avatarPreview" :src="avatarPreview" class="rounded-circle border border-4 border-white shadow-sm" style="width: 110px; height: 110px; object-fit: cover;">
                        <div v-else class="rounded-circle border border-4 border-white shadow-sm bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 110px; height: 110px;">
                            <i class="bi bi-person-fill" style="font-size: 4rem;"></i>
                        </div>
                        
                        <label for="avatarInput" class="position-absolute bottom-0 end-0 btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                            style="width: 30px; height: 30px; cursor: pointer;">
                            <i class="bi bi-camera-fill text-white"></i>
                            <input id="avatarInput" type="file" @change="onFileChange" hidden accept="image/*">
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">ឈ្មោះពេញ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                        <input v-model="form.name" type="text" class="form-control border-start-0 ps-0 shadow-none" :class="{ 'is-invalid': errors.name }" placeholder="បញ្ចូលឈ្មោះពេញ...">
                        <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">ឈ្មោះគណនី (Username)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-at"></i></span>
                        <input v-model="form.username" type="text" class="form-control border-start-0 ps-0 shadow-none" :class="{ 'is-invalid': errors.username }" placeholder="username">
                        <div v-if="errors.username" class="invalid-feedback">{{ errors.username[0] }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">អុីមែល</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                        <input v-model="form.email" type="email" class="form-control border-start-0 ps-0 shadow-none" :class="{ 'is-invalid': errors.email }" placeholder="example@mail.com">
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">លេខទូរស័ព្ទ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                        <input v-model="form.phone" type="text" class="form-control border-start-0 ps-0 shadow-none" :class="{ 'is-invalid': errors.phone }" placeholder="012 345 678">
                        <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone[0] }}</div>
                    </div>
                </div>

                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-5 fw-bold shadow-sm py-2" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-circle me-2"></i>
                        {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការផ្លាស់ប្តូរ' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
    import { reactive, ref, onMounted, onUnmounted } from 'vue';
    import axios from 'axios';
    import Swal from 'sweetalert2';

    const form = reactive({
        name: '',
        username: '',
        email: '',
        phone: '',
        avatar: null
    });

    // ២. State សម្រាប់ UI
    const avatarPreview = ref(null);
    const errors = ref({});
    const loading = ref(false);
    const userRoles = ref([]);

    // ៣. Helper Function សម្រាប់ទាញយក Auth Token
    const getAuthConfig = () => {
        const token = localStorage.getItem('access_token') || localStorage.getItem('token')
        console.log("Current Token:", token);
        return {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'multipart/form-data'
            }
        };
    };

    // ៤. មុខងារទាញយកព័ត៌មាន Profile មកបង្ហាញ (Read)
    const fetchProfile = async () => {
        try {
            const token = localStorage.getItem('access_token');
            if (!token) return;

            const { data } = await axios.get('/api/auth/me', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            form.name = data.name || '';
            form.username = data.username || '';
            form.email = data.email || '';
            form.phone = data.phone || '';

            avatarPreview.value = data.avatar;
            userRoles.value = data.roles || [];
            
        } catch (e) {
            console.error("Fetch Profile Error:", e);
        }
    };

    // ៥. មុខងារជ្រើសរើសរូបភាព (File Handler)
    const onFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
                URL.revokeObjectURL(avatarPreview.value);
            }
            
            form.avatar = file;
            avatarPreview.value = URL.createObjectURL(file);
        }
    };

    // ៦. មុខងាររក្សាទុកការផ្លាស់ប្តូរ (Update)
    const handleUpdate = async () => {
        loading.value = true;
        errors.value = {};
        
        // បង្កើត FormData ព្រោះយើងមានផ្ញើរូបភាព (File)
        const formData = new FormData();
        formData.append('name', form.name);
        formData.append('username', form.username || '');
        formData.append('email', form.email);
        formData.append('phone', form.phone || '');
        
        // បញ្ចូលរូបភាពថ្មី ប្រសិនបើមានការជ្រើសរើស
        if (form.avatar instanceof File) {
            formData.append('avatar', form.avatar);
        }

        try {
            const response = await axios.post('/api/auth/update-profile', formData, getAuthConfig());
            
            // បច្ចុប្បន្នភាពទិន្នន័យលើអេក្រង់ ក្រោយពេល Update ជោគជ័យ
            const updatedUser = response.data.user;
            form.name = updatedUser.name;
            form.username = updatedUser.username;
            form.email = updatedUser.email;
            form.phone = updatedUser.phone;
            avatarPreview.value = updatedUser.avatar;

            // បង្ហាញសារជោគជ័យ
            Swal.fire({
                icon: 'success',
                title: '<span class="khmer-font">ជោគជ័យ</span>',
                text: 'ព័ត៌មានរបស់អ្នកត្រូវបានរក្សាទុក',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

        } catch (err) {
            if (err.response?.status === 422) {
                errors.value = err.response.data.errors;
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'បរាជ័យ', 
                    text: err.response?.data?.message || 'មានបញ្ហាខាងបច្ចេកទេស!' 
                });
            }
        } finally {
            loading.value = false;
        }
    };

    // ៧. Lifecycle Hooks
    onMounted(() => {
        fetchProfile();
    });

    onUnmounted(() => {
        if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
            URL.revokeObjectURL(avatarPreview.value);
        }
    });
</script>

<style scoped>

    .khmer-font {
        font-family: 'Kantumruy Pro', sans-serif !important;
    }

    .input-group-text {
        border-color: #dee2e6;
        color: #6c757d;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .invalid-feedback {
        font-size: 0.8rem;
    }

    .btn-primary {
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }
</style>