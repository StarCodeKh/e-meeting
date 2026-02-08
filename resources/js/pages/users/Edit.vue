<template>
<div>
<h2>Edit User</h2>
<form @submit.prevent="submit">
<input v-model="form.name" />
<input v-model="form.email" />
<input v-model="form.password" type="password" placeholder="New Password" />
<button>Update</button>
</form>
</div>
</template>


<script setup>
import { reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api/axios'

const route = useRoute()
const router = useRouter()


const form = reactive({
name: '',
email: '',
password: ''
})


onMounted(async () => {
const res = await api.get(`/users/${route.params.id}`)
form.name = res.data.name
form.email = res.data.email
})


const submit = async () => {
await api.put(`/users/${route.params.id}`, form)
router.push('/users')
}
</script>