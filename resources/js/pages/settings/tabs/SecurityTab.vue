<template>
	<form @submit.prevent="handleUpdate">
		<div class="row g-3">
			<div class="col-12">
				<label class="form-label khmer-font small fw-bold text-secondary">
					លេខសម្ងាត់ថ្មី <span class="text-danger">*</span>
				</label>
				<div class="input-group">
					<span class="input-group-text bg-light border-end-0 text-muted">
						<i class="bi bi-shield-lock"></i>
					</span>
					<input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="form-control border-start-0 ps-0 shadow-none khmer-font" :class="{ 'is-invalid': errors.password }" placeholder="បញ្ចូលលេខសម្ងាត់ថ្មី" required>
					<button type="button" class="input-group-text bg-white text-muted" @click="showPassword = !showPassword">
						<i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
					</button>
					<div v-if="errors.password" class="invalid-feedback animate__animated animate__shakeX">
						{{ errors.password[0] }}
					</div>
				</div>
			</div>

			<div class="col-12">
				<label class="form-label khmer-font small fw-bold text-secondary">
					បញ្ជាក់លេខសម្ងាត់ <span class="text-danger">*</span>
				</label>
				<div class="input-group">
					<span class="input-group-text bg-light border-end-0 text-muted">
						<i class="bi bi-check2-circle"></i>
					</span>
					<input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation" class="form-control border-start-0 ps-0 shadow-none khmer-font" placeholder="បញ្ជាក់លេខសម្ងាត់ថ្មីម្តងទៀត" required>
					<button type="button" class="input-group-text bg-white text-muted" @click="showConfirmPassword = !showConfirmPassword">
						<i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
					</button>
				</div>
			</div>

			<div class="col-12 text-end pt-4">
				<div class="d-flex justify-content-between align-items-center">
					<small class="text-muted khmer-font x-small text-start">
						<i class="bi bi-info-circle me-1"></i> លេខសម្ងាត់គួរមានយ៉ាងតិច ៨ តួអក្សរ និងមានលក្ខណៈស្មុគស្មាញ។
					</small>
					<button type="submit" class="btn btn-danger rounded-3 px-4 shadow-sm fw-bold khmer-font py-2" :disabled="loading">
						<span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
						<i v-else class="bi bi-key-fill me-2"></i>
						{{ loading ? 'កំពុងរក្សាទុក...' : 'ប្តូរលេខសម្ងាត់' }}
					</button>
				</div>
			</div>
		</div>
	</form>
</template>

<script setup>
    import { reactive, ref } from 'vue'
    import axios from 'axios'
    import Swal from 'sweetalert2'

    const form = reactive({ 
        password: '', 
        password_confirmation: '' 
    })
    
    const errors = ref({})
    const loading = ref(false)
    
    // បន្ថែម states សម្រាប់ប៊ូតុងបង្ហាញ/លាក់លេខសម្ងាត់
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)

    const handleUpdate = async () => {
        if (form.password !== form.password_confirmation) {
            errors.value = { password: ['លេខសម្ងាត់ទាំងពីរមិនត្រូវគ្នាទេ!'] }
            return
        }

        loading.value = true
        errors.value = {}
        
        try {
            const res = await axios.post('/settings/password', form)
        
            Swal.fire({
                icon: 'success',
                title: 'កែប្រែជោគជ័យ',
                text: 'លេខសម្ងាត់របស់អ្នកត្រូវបានផ្លាស់ប្តូររួចរាល់',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            })

            form.password = ''
            form.password_confirmation = ''
            
        } catch (err) {
            if (err.response?.status === 422) {
                errors.value = err.response.data.errors
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'បរាជ័យ',
                    text: err.response?.data?.message || 'មានបញ្ហាបច្ចេកទេស!'
                })
            }
        } finally {
            loading.value = false
        }
    }
</script>