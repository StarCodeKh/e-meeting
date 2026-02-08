<template>
    <div class="login-page">
        <div class="login-wrapper">
            <form class="login-card shadow" @submit.prevent="handleLogin">
                <div class="card-header text-center mb-4">
                    <!-- <div class="logo-squircle mb-3">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div> -->
                    <div class="logo-squircle mb-3">
                        <img :src="logo" alt="Logo" class="logo-img">
                    </div>
                    <h2 class="khmer-font fw-bold text-dark">ចូលប្រើប្រាស់ប្រព័ន្ធ</h2>
                    <p class="text-muted khmer-font small">សូមបញ្ចូលគណនីរបស់អ្នកដើម្បីបន្តទៅកាន់ផ្ទាំងគ្រប់គ្រង</p>
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

                <div class="form-group mb-4">
                    <label class="khmer-font mb-2" for="password">លេខសម្ងាត់ (Password)</label>
                    <div class="input-with-icon">
                        <i class="bi bi-lock"></i>
                        <input 
                        id="password"
                        v-model="form.password" 
                        type="password" 
                        placeholder="បញ្ចូលលេខសម្ងាត់" 
                        required
                        :disabled="loading"
                        />
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="btn-login-gradient w-100 mb-3"
                    :disabled="loading"
                    >
                    <div v-if="loading" class="spinner-border spinner-border-sm me-2"></div>
                    <span class="khmer-font fw-bold">
                        {{ loading ? 'កំពុងផ្ទៀងផ្ទាត់...' : 'ចូលប្រើប្រាស់' }}
                    </span>
                </button>

                <p class="footer-text text-center khmer-font mt-2">
                    មិនទាន់មានគណនី?
                    <RouterLink to="/auth/register" class="text-primary-link text-decoration-none fw-bold">
                        ចុះឈ្មោះនៅទីនេះ
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
    import logo from '@/assets/images/logo.png';

    const router = useRouter()
    const loading = ref(false)
    const publicKey = ref(null)

    const form = reactive({
        email: '',
        password: ''
    })

    /**
     * FETCH PUBLIC KEY FROM API
     * Standard security practice to encrypt password before sending
     */
    const fetchPublicKey = async () => {
        try {
            const res = await api.get('/public-key')
            publicKey.value = res.data.public_key
        } catch (error) {
            alertStore.show('មិនអាចទាញយកសោសម្ងាត់ (Public Key) បានទេ។', 'error')
            console.error('RSA Key Error:', error)
        }
    }

    /**
     * DYNAMIC LOGIN LOGIC
     */
    const handleLogin = async () => {
        if (!publicKey.value) {
            alertStore.show('ប្រព័ន្ធមិនទាន់រួចរាល់ សូមព្យាយាមម្តងទៀតក្នុងពេលឆាប់ៗ។', 'error')
            return
        }

        loading.value = true

        try {
            // 1. Encrypt Password for Security
            const encrypt = new JSEncrypt()
            encrypt.setPublicKey(publicKey.value)
            const encryptedPassword = encrypt.encrypt(form.password)

            if (!encryptedPassword) {
            throw new Error('Encryption failed')
            }

            // 2. API Call
            const response = await api.post('/auth/login', {
            email: form.email,
            password: encryptedPassword,
            })

            // 3. Save Token & User Data
            const token = response.data.access_token || response.data.token
            localStorage.setItem('token', token)
            
            if (response.data.user) {
            localStorage.setItem('user', JSON.stringify(response.data.user))
            }

            // 4. Success Notification & Redirect
            alertStore.show('ចូលប្រើប្រាស់ទទួលបានជោគជ័យ!', 'success')
            router.push('/')

        } catch (error) {
            const message = error.response?.data?.message || 'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវ!'
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
    /* 1. Use swap for better performance and local fallbacks */
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;700&display=swap');

    .khmer-font {
        font-family: 'Noto Sans Khmer', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        -webkit-font-smoothing: antialiased; /* Smoother Khmer text */
        -moz-osx-font-smoothing: grayscale;
    }

    .login-page {
        min-height: 100vh;
        background-color: #f1f5f9;
        padding: 24px 16px;
        display: grid;
        place-items: center; 
        overflow-x: hidden; 
    }

    .logo-squircle {
        width: 65px;
        height: 65px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border-radius: 18px;
        padding: 10px; 
    }

    .logo-img {
        width: 100%;  
        height: 100%;
        object-fit: contain; 
    }

    .login-wrapper {
        width: 100%;
        display: contents;
    }

    .login-card {
        background: #ffffff;
        padding: 48px 40px; /* Standard spacious padding */
        border-radius: 24px;
        width: 100%;
        max-width: 500px; 
        border: none;
        margin: auto; 
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        transition: transform 0.3s ease; /* Subtle entry feel */
    }

    .logo-circle {
        width: 64px;
        height: 64px;
        background: #eef2ff;
        color: #4285f4;
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border-radius: 18px; /* Standard Squircle */
    }

    /* Form Styles */
    label {
        display: block;
        font-size: 0.9rem;
        color: #475569;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .input-with-icon {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-with-icon i {
        position: absolute;
        left: 18px;
        /* Remove top: 50% and use flex alignment for better mobile stability */
        color: #94a3b8;
        font-size: 1.25rem;
        pointer-events: none; /* Icon won't block click */
    }

    input {
        width: 100%;
        padding: 14px 16px 14px 52px; /* Balanced padding for icon */
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        outline: none;
        background: #ffffff;
        font-size: 1rem; /* Prevents auto-zoom on iOS */
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Noto Sans Khmer', sans-serif;
    }

    input:focus {
        border-color: #4285f4;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.12);
    }

    /* Button Gradient */
    .btn-login-gradient {
        background: linear-gradient(135deg, #4285f4 0%, #63b3ed 100%);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 14px;
        font-size: 1.1rem;
        font-weight: 700;
        width: 100%;
        cursor: pointer;
        user-select: none;
        -webkit-tap-highlight-color: transparent; /* Clean mobile tap */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-login-gradient:hover:not(:disabled) {
        filter: brightness(1.08);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(66, 133, 244, 0.25);
    }

    .btn-login-gradient:active {
        transform: scale(0.97);
    }

    .btn-login-gradient:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Responsive - Full Mobile Optimization */
    @media (max-width: 576px) {
        .login-page {
            padding: 16px;
        }
        
        .login-card {
            padding: 32px 24px;
            border-radius: 20px;
            max-width: 100%;
        }

        .logo-circle {
            width: 56px;
            height: 56px;
            font-size: 1.75rem;
        }

        input {
            padding: 12px 14px 12px 48px;
            font-size: 16px; /* Crucial: stops iOS zoom-in on focus */
        }
    }
</style>