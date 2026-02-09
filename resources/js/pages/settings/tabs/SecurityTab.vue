<template>
  <form @submit.prevent="handleUpdate">
    <div class="row g-4">
      <div class="col-12">
        <label class="form-label khmer-font small fw-bold text-secondary">លេខសម្ងាត់ថ្មី</label>
        <input 
          type="password" v-model="form.password"
          class="form-control form-control-pill" 
          :class="{ 'is-invalid': errors.password }"
        >
        <div v-if="errors.password" class="invalid-feedback">{{ errors.password[0] }}</div>
      </div>

      <div class="col-12">
        <label class="form-label khmer-font small fw-bold text-secondary">បញ្ជាក់លេខសម្ងាត់</label>
        <input 
          type="password" v-model="form.password_confirmation"
          class="form-control form-control-pill"
        >
      </div>

      <div class="col-12 text-end pt-3">
        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold khmer-font" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'កំពុងរក្សាទុក...' : 'ប្តូរលេខសម្ងាត់' }}
        </button>
      </div>
    </div>
  </form>
</template>

<script setup>
    import { reactive, ref } from 'vue'
    import axios from 'axios'

    const form = reactive({ password: '', password_confirmation: '' })
    const errors = ref({})
    const loading = ref(false)

    const handleUpdate = async () => {
        loading.value = true
        errors.value = {}
        try {
            await axios.post('/api/settings/password', form)
            alert("ជោគជ័យ!")
            form.password = ''; form.password_confirmation = ''
        } catch (err) {
            if (err.response?.status === 422) errors.value = err.response.data.errors
        } finally {
            loading.value = false
        }
    }
</script>