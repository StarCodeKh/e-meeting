<template>
  <div class="register-page">
    <div class="register-wrapper">
      <form class="register-card shadow" @submit.prevent="handleRegister">
        
        <div class="card-header text-center mb-4">
          <div class="logo-squircle mb-3">
            <i class="bi bi-person-plus-fill"></i>
          </div>
          <h2 class="khmer-font fw-bold text-dark">បង្កើតគណនីថ្មី</h2>
          <p class="text-muted khmer-font small">សូមបំពេញព័ត៌មានខាងក្រោមដើម្បីចុះឈ្មោះ</p>
        </div>

        <div class="form-group mb-3">
          <label class="khmer-font mb-2" for="name">ឈ្មោះពេញ (Full Name)</label>
          <div class="input-with-icon">
            <i class="bi bi-person"></i>
            <input 
              id="name"
              v-model="form.name" 
              type="text" 
              placeholder="បញ្ចូលឈ្មោះរបស់អ្នក" 
              required
              :disabled="loading"
            />
          </div>
        </div>

        <div class="form-group mb-3">
          <label class="khmer-font mb-2" for="email">អ៊ីមែល (Email)</label>
          <div class="input-with-icon">
            <i class="bi bi-envelope"></i>
            <input 
              id="email"
              v-model="form.email" 
              type="email" 
              placeholder="បញ្ចូលអ៊ីមែលរបស់អ្នក" 
              required
              :disabled="loading"
            />
          </div>
        </div>

        <div class="form-group mb-3">
          <label class="khmer-font mb-2" for="password">លេខសម្ងាត់ (Password)</label>
          <div class="input-with-icon">
            <i class="bi bi-lock"></i>
            <input 
              id="password"
              v-model="form.password" 
              type="password" 
              placeholder="បង្កើតលេខសម្ងាត់" 
              required
              :disabled="loading"
            />
          </div>
        </div>

        <div class="form-group mb-4">
          <label class="khmer-font mb-2" for="confirm_password">បញ្ជាក់លេខសម្ងាត់</label>
          <div class="input-with-icon">
            <i class="bi bi-shield-check"></i>
            <input 
              id="confirm_password"
              v-model="form.password_confirmation" 
              type="password" 
              placeholder="បញ្ចូលលេខសម្ងាត់ម្តងទៀត" 
              required
              :disabled="loading"
            />
          </div>
        </div>

        <button 
          type="submit" 
          class="btn-register-gradient w-100 mb-3"
          :disabled="loading"
        >
          <div v-if="loading" class="spinner-border spinner-border-sm me-2"></div>
          <span class="khmer-font fw-bold">
            {{ loading ? 'កំពុងបង្កើត...' : 'ចុះឈ្មោះឥឡូវនេះ' }}
          </span>
        </button>

        <p class="footer-text text-center khmer-font mt-2">
          មានគណនីរួចហើយ?
          <RouterLink to="/auth/login" class="text-primary-link text-decoration-none fw-bold">
            ចូលប្រើប្រាស់វិញ
          </RouterLink>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import { alertStore } from '@/stores/alert'
import JSEncrypt from 'jsencrypt'

const router = useRouter()
const loading = ref(false)
const publicKey = ref(null)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const fetchPublicKey = async () => {
  try {
    const res = await api.get('/public-key')
    publicKey.value = res.data.public_key
  } catch (error) {
    alertStore.show('មិនអាចទាញយកសោសម្ងាត់បានទេ។', 'error')
  }
}

const handleRegister = async () => {
  // 1. Basic Validation
  if (form.password !== form.password_confirmation) {
    alertStore.show('លេខសម្ងាត់ទាំងពីរមិនដូចគ្នាទេ!', 'error')
    return
  }

  if (!publicKey.value) {
    alertStore.show('ប្រព័ន្ធមិនទាន់រួចរាល់ សូមព្យាយាមម្តងទៀត។', 'error')
    return
  }

  loading.value = true

  try {
    // 2. Encrypt Password
    const encrypt = new JSEncrypt()
    encrypt.setPublicKey(publicKey.value)
    const encryptedPassword = encrypt.encrypt(form.password)

    // 3. API Call
    await api.post('/auth/register', {
      name: form.name,
      email: form.email,
      password: encryptedPassword,
      password_confirmation: encryptedPassword, // Note: Encrypt both for security
    })

    alertStore.show('បង្កើតគណនីជោគជ័យ! សូមចូលប្រើប្រាស់។', 'success')
    router.push('/auth/login')

  } catch (error) {
    const message = error.response?.data?.message || 'ការចុះឈ្មោះមិនបានជោគជ័យទេ'
    alertStore.show(message, 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPublicKey()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;700&display=swap');

.khmer-font { font-family: 'Noto Sans Khmer', sans-serif; }

.register-page {
    min-height: 100vh;
    background-color: #f1f5f9;
    padding: 20px;
    display: grid;
    place-items: center; 
}

.register-card {
  background: #ffffff;
  padding: 45px;
  border-radius: 24px;
  width: 100%;
  max-width: 500px;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04) !important;
}

.logo-squircle {
  width: 65px;
  height: 65px;
  background: #eef2ff;
  color: #4285f4;
  font-size: 2.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  border-radius: 18px;
}

label {
  display: block;
  font-size: 0.95rem;
  color: #475569;
  font-weight: 600;
}

.input-with-icon { position: relative; }

.input-with-icon i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.2rem;
}

input {
  width: 100%;
  padding: 14px 15px 14px 50px;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  outline: none;
  transition: all 0.2s ease;
  font-family: 'Noto Sans Khmer', sans-serif;
}

input:focus {
  border-color: #4285f4;
  box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.1);
}

.btn-register-gradient {
  background: linear-gradient(135deg, #4285f4 0%, #63b3ed 100%);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 14px;
  font-size: 1.15rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.btn-register-gradient:hover:not(:disabled) {
  filter: brightness(1.05);
  transform: translateY(-1px);
}

.btn-register-gradient:active { transform: scale(0.98); }

.text-primary-link { color: #4285f4; }

@media (max-width: 576px) {
  .register-card { padding: 30px 20px; }
}
</style>