<template>
<div class="container">
<h2>User List</h2>
<router-link to="/users/create">Create User</router-link>


<table border="1" width="100%">
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Action</th>
</tr>
<tr v-for="user in users" :key="user.id">
<td>{{ user.id }}</td>
<td>{{ user.name }}</td>
<td>{{ user.email }}</td>
<td>
<router-link :to="`/users/${user.id}/edit`">Edit</router-link>
<button @click="deleteUser(user.id)">Delete</button>
</td>
</tr>
</table>
</div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/axios'


const users = ref([])


const fetchUsers = async () => {
const res = await api.get('/users')
users.value = res.data
}


const deleteUser = async (id) => {
if (!confirm('Delete this user?')) return
await api.delete(`/users/${id}`)
fetchUsers()
}


onMounted(fetchUsers)
</script>