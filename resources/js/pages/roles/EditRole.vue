<template>
  <div class="role-edit-page">
    <h2>Edit Role</h2>

    <form @submit.prevent="updateRole">
      <input
        type="text"
        v-model="form.name"
        placeholder="Role name"
        autocomplete="off"
      />

      <span v-if="error" class="error">{{ error }}</span>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Updating...' : 'Update Role' }}
      </button>
      <button type="button" @click="goBack" class="btn-cancel">Cancel</button>
    </form>

    <p v-if="success" class="success">{{ success }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api/axios'

const route = useRoute()
const router = useRouter()

const form = ref({
  name: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function fetchRole() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/roles/${route.params.id}`)
    form.value.name = res.data.data.name
  } catch {
    error.value = 'Failed to load role data.'
  } finally {
    loading.value = false
  }
}

async function updateRole() {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const res = await api.put(`/roles/${route.params.id}`, form.value)
    success.value = res.data.message
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

function goBack() {
  router.back()
}

onMounted(() => {
  fetchRole()
})
</script>

<style scoped>
.role-edit-page {
  max-width: 400px;
  margin: auto;
  padding: 20px;
}

input {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
}

button {
  padding: 10px 15px;
  margin-right: 10px;
}

.btn-cancel {
  background-color: #6c757d;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}

.btn-cancel:hover {
  background-color: #5a6268;
}

.error {
  color: red;
  font-size: 14px;
  margin-bottom: 10px;
}

.success {
  color: green;
  margin-top: 10px;
}
</style>
