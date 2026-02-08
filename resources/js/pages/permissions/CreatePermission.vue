<template>
  <div class="permission-page">
    <h2>Create Permission</h2>

    <form @submit.prevent="createPermission">
      <input
        type="text"
        v-model="form.name"
        placeholder="Permission name"
      />

      <span v-if="error" class="error">{{ error }}</span>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Saving...' : 'Create Permission' }}
      </button>
    </form>

    <p v-if="success" class="success">{{ success }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../../api/axios'

const form = ref({
  name: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function createPermission() {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const res = await api.post('/permissions', form.value)

    success.value = res.data.message
    form.value.name = ''

  } catch (err) {
    if (err.response?.status === 409) {
      error.value = err.response.data.message
    } else if (err.response?.status === 422) {
      error.value = err.response.data.errors.name[0]
    } else {
      error.value = 'Something went wrong'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.permission-page {
  max-width: 400px;
}

input {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
}

button {
  padding: 10px 15px;
}

.error {
  color: red;
  font-size: 14px;
}

.success {
  color: green;
  margin-top: 10px;
}
</style>
