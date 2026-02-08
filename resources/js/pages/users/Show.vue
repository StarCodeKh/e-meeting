<template>
<div class="container">
<h2>User Details</h2>


<p><strong>ID:</strong> {{ user.id }}</p>
<p><strong>Name:</strong> {{ user.name }}</p>
<p><strong>Email:</strong> {{ user.email }}</p>


<router-link to="/users">Back</router-link>
</div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api/axios'


const route = useRoute()
const user = ref({})


onMounted(async () => {
const res = await api.get(`/users/${route.params.id}`)
user.value = res.data
})
</script>