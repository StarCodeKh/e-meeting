<template>
    <form @submit.prevent="handleUpdate">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label khmer-font small fw-bold text-secondary">
                    លេខសម្ងាត់ចាស់ <span class="text-danger">*</span>
                </label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <input :type="showOld ? 'text' : 'password'" v-model="form.old_password" 
                           class="form-control border-start-0 ps-0 shadow-none khmer-font" 
                           :class="{ 'is-invalid': errors.old_password }" placeholder="បញ្ចូលលេខសម្ងាត់ចាស់" required>
                    <button type="button" class="input-group-text bg-white text-muted" @click="showOld = !showOld">
                        <i :class="showOld ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                    <div v-if="errors.old_password" class="invalid-feedback animate__animated animate__fadeIn">
                        {{ errors.old_password[0] }}
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label khmer-font small fw-bold text-secondary">
                    លេខសម្ងាត់ថ្មី <span class="text-danger">*</span>
                </label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-key"></i>
                    </span>
                    <input :type="showPassword ? 'text' : 'password'" v-model="form.password" 
                           class="form-control border-start-0 ps-0 shadow-none khmer-font" 
                           :class="{ 'is-invalid': errors.password }" placeholder="បញ្ចូលលេខសម្ងាត់ថ្មី" required>
                    <button type="button" class="input-group-text bg-white text-muted" @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                    <div v-if="errors.password" class="invalid-feedback animate__animated animate__fadeIn">
                        {{ errors.password[0] }}
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label khmer-font small fw-bold text-secondary">
                    បញ្ជាក់លេខសម្ងាត់ថ្មី <span class="text-danger">*</span>
                </label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-check2-all"></i>
                    </span>
                    <input :type="showConfirm ? 'text' : 'password'" v-model="form.password_confirmation" 
                           class="form-control border-start-0 ps-0 shadow-none khmer-font" placeholder="បញ្ជាក់លេខសម្ងាត់ថ្មីម្តងទៀត" required>
                    <button type="button" class="input-group-text bg-white text-muted" @click="showConfirm = !showConfirm">
                        <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="col-12 pt-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <small class="text-muted khmer-font small">
                        <i class="bi bi-info-circle-fill text-info me-1"></i> ត្រូវមានយ៉ាងតិច ៨ តួអក្សរ។
                    </small>
                    <button type="submit" class="btn btn-primary rounded-3 px-5 shadow-sm fw-bold khmer-font py-2" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-shield-check me-2"></i>
                        {{ loading ? 'កំពុងរក្សាទុក...' : 'ផ្លាស់ប្តូរឥឡូវនេះ' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<script setup>
	import { reactive, ref, onMounted, computed } from 'vue'
	import axios from 'axios'
	import Swal from 'sweetalert2'
	import JSEncrypt from 'jsencrypt'

	// --- ១. State Management ---
	const form = reactive({ 
		old_password: '',
		password: '', 
		password_confirmation: '' 
	})

	const errors = ref({})
	const loading = ref(false)
	const publicKey = ref(null)

	const showOld = ref(false)
	const showPassword = ref(false)
	const showConfirm = ref(false)

	// --- ២. Utilities ---
	const crypt = new JSEncrypt()

	const encryptData = (data) => {
		if (!publicKey.value) return null
		crypt.setPublicKey(publicKey.value)
		return crypt.encrypt(data)
	}

	const showAlert = (title, text, icon = 'success') => {
		Swal.fire({
			title: `<span class="khmer-font">${title}</span>`,
			html: `<span class="khmer-font">${text}</span>`,
			icon: icon,
			confirmButtonText: 'យល់ព្រម',
			customClass: {
				confirmButton: 'btn btn-primary khmer-font px-5 rounded-3',
				popup: 'rounded-4 border-0 shadow-lg'
			},
			buttonsStyling: false
		})
	}

	// --- ៣. API Actions ---
	const fetchPublicKey = async () => {
		try {
			const { data } = await axios.get('/api/auth/public-key') 
			if (data.public_key) publicKey.value = data.public_key
		} catch (error) {
			console.error('RSA Key Error:', error)
		}
	}

	const handleUpdate = async () => {
		if (form.password !== form.password_confirmation) {
			errors.value = { password: ['លេខសម្ងាត់ថ្មីទាំងពីរមិនត្រឹមត្រូវគ្នាទេ!'] }
			return
		}

		if (!publicKey.value) {
			showAlert('បញ្ហាប្រព័ន្ធ', 'មិនទាន់អាចទាញយកសោសម្ងាត់បានឡើយ។', 'error')
			return
		}

		loading.value = true
		errors.value = {}
		
		try {
			const token = localStorage.getItem('access_token') || localStorage.getItem('token')

			const securePayload = {
				old_password: encryptData(form.old_password),
				password: encryptData(form.password),
				password_confirmation: encryptData(form.password_confirmation)
			}

			const res = await axios.post('/api/auth/change-password', securePayload, {
				headers: {
					Authorization: `Bearer ${token}`,
					Accept: 'application/json'
				}
			})
		
			showAlert('ជោគជ័យ', res.data.message || 'លេខសម្ងាត់ត្រូវបានប្តូររួចរាល់!')
			
			// Reset Form ប្រើវិធី Reset Array ឱ្យលឿន
			Object.assign(form, { old_password: '', password: '', password_confirmation: '' })
			
		} catch (err) {
			const status = err.response?.status
			const data = err.response?.data

			if (status === 401) {
				showAlert('បញ្ហាចូលប្រើ', 'Token របស់បងអស់សុពលភាព។ សូមចូលប្រើប្រាស់ម្ដងទៀត!', 'warning')
			} else if (status === 422) {
				errors.value = data.errors || {}
			} else {
				showAlert('បរាជ័យ', data?.message || 'លេខសម្ងាត់ចាស់មិនត្រឹមត្រូវ!', 'error')
			}
		} finally {
			loading.value = false
		}
	}

	onMounted(fetchPublicKey)
</script>

<style scoped>
	.khmer-font {
		font-family: 'Kantumruy Pro', sans-serif;
	}
</style>