<template>
  <div>
    <h1>Create Action</h1>

    <form @submit.prevent="submitForm">
      <div>
        <label for="name">Name (key):</label>
        <input id="name" v-model="form.name" required />
      </div>

      <div>
        <label for="label">Label:</label>
        <input id="label" v-model="form.label" />
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
  name: '',
  label: ''
})

const error = ref('')

const submitForm = async () => {
  error.value = ''
  try {
    const res = await fetch('/api/actions', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    })

    if (!res.ok) {
      const errData = await res.json()
      throw new Error(errData.message || 'Failed to create action')
    }

    router.push({ name: 'actions.index' })
  } catch (err) {
    error.value = err.message
  }
}

const cancel = () => {
  router.push({ name: 'actions.index' })
}
</script>
