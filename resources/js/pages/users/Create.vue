<template>
  <div>
    <h2>Create User</h2>

    <form @submit.prevent="submit">
      <input v-model="form.name" placeholder="Name" />
      <br />

      <input v-model="form.email" placeholder="Email" />
      <br />

      <input
        v-model="form.password"
        type="password"
        placeholder="Password"
      />
      <br />

      <button type="submit">Create</button>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api/axios'

const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: ''
})

const submit = async () => {
  await api.post('/users', form)
  router.push('/users')
}
</script>
