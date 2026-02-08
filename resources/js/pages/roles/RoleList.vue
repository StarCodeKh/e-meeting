<template>
  <div class="role-list-page">
    <h2>Roles List</h2>

    <button @click="$router.push({ name: 'roles.create' })" class="btn-create">
      + Create New Role
    </button>

    <div v-if="loading" class="loading">Loading roles...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-if="roles.length && !loading" class="role-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Role Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="role in roles" :key="role.id">
          <td>{{ role.id }}</td>
          <td>{{ role.name }}</td>
          <td>
            <button @click="editRole(role.id)" class="btn-edit">Edit</button>
            <button @click="confirmDelete(role.id)" class="btn-delete">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else-if="!loading">No roles found.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/axios'

const roles = ref([])
const loading = ref(false)
const error = ref('')

async function fetchRoles() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/roles')
    roles.value = res.data.data
  } catch {
    error.value = 'Failed to load roles.'
  } finally {
    loading.value = false
  }
}

function editRole(id) {
  // Navigate to edit page (make sure route exists)
  // For example: /roles/edit/:id
  window.location.href = `/roles/edit/${id}`
  // or use Vue Router if available:
  // router.push({ name: 'roles.edit', params: { id } })
}

async function deleteRole(id) {
  try {
    await api.delete(`/roles/${id}`)
    roles.value = roles.value.filter(role => role.id !== id)
    alert('Role deleted successfully.')
  } catch {
    alert('Failed to delete role.')
  }
}

function confirmDelete(id) {
  if (confirm('Are you sure you want to delete this role?')) {
    deleteRole(id)
  }
}

onMounted(() => {
  fetchRoles()
})
</script>

<style scoped>
.role-list-page {
  max-width: 700px;
  margin: auto;
  padding: 20px;
}

.btn-create {
  margin-bottom: 15px;
  padding: 8px 16px;
  background-color: #2c7be5;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}

.btn-create:hover {
  background-color: #1a5bb8;
}

.loading {
  margin-bottom: 10px;
  font-style: italic;
}

.error {
  color: red;
  margin-bottom: 10px;
}

.role-table {
  width: 100%;
  border-collapse: collapse;
}

.role-table th,
.role-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.role-table th {
  background-color: #f4f4f4;
}

.btn-edit,
.btn-delete {
  padding: 5px 10px;
  margin-right: 5px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-edit {
  background-color: #ffc107;
  color: #212529;
}

.btn-edit:hover {
  background-color: #e0a800;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

.btn-delete:hover {
  background-color: #a71d2a;
}
</style>
