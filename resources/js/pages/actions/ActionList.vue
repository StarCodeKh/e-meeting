<template>
  <div class="actions-list">
    <h1>Actions List</h1>
    <button class="btn-create" @click="goCreate">+ Create Action</button>

    <table class="actions-table" v-if="actions.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Label</th>
          <th>Sort Order</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="action in actions" :key="action.id">
          <td>{{ action.id }}</td>
          <td>{{ action.name }}</td>
          <td>{{ action.label || '-' }}</td>
          <td>{{ action.sort_order }}</td>
          <td>{{ new Date(action.created_at).toLocaleDateString() }}</td>
        </tr>
      </tbody>
    </table>

    <p v-else>No actions found.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios' // Your axios instance

const actions = ref([])
const router = useRouter()

const fetchActions = async () => {
  try {
    const res = await api.get('/actions')
    actions.value = res.data.data || []
  } catch (err) {
    console.error('Failed to load actions:', err)
  }
}

const goCreate = () => {
  router.push({ name: 'actions.create' })
}

onMounted(() => {
  fetchActions()
})
</script>

<style scoped>
.actions-list {
  max-width: 900px;
  margin: 0 auto;
  padding: 1rem;
  font-family: Arial, sans-serif;
}

h1 {
  margin-bottom: 1rem;
  font-weight: 700;
  color: #333;
}

.btn-create {
  margin-bottom: 1rem;
  padding: 8px 16px;
  background-color: #0d6efd;
  border: none;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.btn-create:hover {
  background-color: #0b5ed7;
}

.actions-table {
  width: 100%;
  border-collapse: collapse;
  box-shadow: 0 0 8px rgb(0 0 0 / 0.1);
  border-radius: 5px;
  overflow: hidden;
}

.actions-table th,
.actions-table td {
  border: 1px solid #ddd;
  padding: 10px 12px;
  text-align: left;
}

.actions-table th {
  background-color: #f1f3f5;
  font-weight: 600;
  color: #555;
}
</style>
