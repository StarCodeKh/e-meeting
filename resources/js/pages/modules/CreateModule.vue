<template>
  <div>
    <h1>Create Module</h1>

    <form @submit.prevent="submitForm">
      <div>
        <label for="key_name">Key Name:</label>
        <input id="key_name" v-model="form.key_name" required />
      </div>

      <div>
        <label for="label">Label:</label>
        <input id="label" v-model="form.label" required />
      </div>

      <button type="submit">Create</button>
      <button type="button" @click="cancel">Cancel</button>
    </form>

    <p v-if="error" style="color: red;">{{ error }}</p>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = reactive({
  key_name: '',  // changed from key to key_name to match backend
  label: ''
})

const error = ref('')

const submitForm = async () => {
  error.value = ''
  try {
    const res = await fetch('/api/modules', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    })

    if (!res.ok) {
      const errData = await res.json()
      throw new Error(errData.message || 'Failed to create module')
    }

    router.push({ name: 'modules.index' })
  } catch (err) {
    error.value = err.message
  }
}

const cancel = () => {
  router.push({ name: 'modules.index' })
}
</script>
