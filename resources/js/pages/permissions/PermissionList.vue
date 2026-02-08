<template>
  <div class="permission-list-page">
    <div class="header">
      <h2>Permissions</h2>
      <button @click="$router.push({ name: 'permissions.create' })">
        + Create Permission
      </button>
    </div>

    <div v-if="loading">Loading permissions...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-if="modules.length" class="permission-table">
      <thead>
        <tr>
          <th>Module</th>
          <th v-for="action in actions" :key="action" class="action-header">
            {{ action }}
          </th>
          <th>Actions</th> <!-- Single actions column per module -->
        </tr>
      </thead>

      <tbody>
        <tr v-for="module in modules" :key="module">
          <td class="module-name">{{ module }}</td>

          <!-- Show badges for each action -->
          <td v-for="action in actions" :key="action" class="action-cell">
            <template v-if="hasPermission(module, action)">
              <span class="badge success">✔</span>
            </template>
            <template v-else>
              <span class="badge muted">—</span>
            </template>
          </td>

          <!-- Edit/Delete buttons for the whole module -->
          <td class="actions-cell">
            <button
              @click="handleEditModule(module)"
              class="btn btn-edit"
              title="Edit all permissions in this module"
            >
              Edit
            </button>
            <button
              @click="handleDeleteModule(module)"
              class="btn btn-delete"
              title="Delete all permissions in this module"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else-if="!loading">No permissions found.</div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../../api/axios'

const permissions = ref([])
const loading = ref(false)
const error = ref('')

async function fetchPermissions() {
  loading.value = true
  try {
    const res = await api.get('/permissions')
    permissions.value = res.data.data
  } catch (e) {
    error.value = 'Failed to load permissions'
  } finally {
    loading.value = false
  }
}

const modules = computed(() => {
  const set = new Set()
  permissions.value.forEach(p => {
    const [module] = p.name.split('.')
    set.add(module)
  })
  return Array.from(set)
})

const actions = computed(() => {
  const set = new Set()
  permissions.value.forEach(p => {
    const [, action] = p.name.split('.')
    set.add(action)
  })
  return Array.from(set)
})

const permissionsByModule = computed(() => {
  const map = {}
  permissions.value.forEach(p => {
    const [module] = p.name.split('.')
    if (!map[module]) map[module] = []
    map[module].push(p)
  })
  return map
})

function hasPermission(module, action) {
  return permissions.value.some(p => p.name === `${module}.${action}`)
}

function handleEditModule(module) {
  // Redirect to edit page - here just sending first permission's id for example
  const perm = permissionsByModule.value[module][0]
  if (perm) {
    $router.push({ name: 'permissions.edit', params: { id: perm.id } })
  }
}

async function handleDeleteModule(module) {
  if (!confirm(`Are you sure to delete all permissions in "${module}"?`)) return
  try {
    loading.value = true
    const toDelete = permissionsByModule.value[module]
    for (const perm of toDelete) {
      await api.delete(`/permissions/${perm.id}`)
    }
    await fetchPermissions()
  } catch {
    error.value = 'Failed to delete permissions'
  } finally {
    loading.value = false
  }
}

onMounted(fetchPermissions)
</script>

<style scoped>
.permission-list-page {
  background: #fff;
  padding: 20px;
  border-radius: 6px;
  border: 1px solid #ddd;
  font-family: Arial, Helvetica, sans-serif;
  color: #333;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: bold;
}

button {
  background: #0d6efd;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

button:hover {
  background: #0b5ed7;
}

.permission-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  table-layout: fixed;
}

.permission-table thead {
  background: #f8f9fa;
}

.permission-table th,
.permission-table td {
  border: 1px solid #dee2e6;
  padding: 10px;
  text-align: center;
  vertical-align: middle;
  word-wrap: break-word;
}

.permission-table th {
  text-transform: capitalize;
}

.module-name {
  font-weight: 600;
  text-align: left;
  background: #fafafa;
}

.badge {
  display: inline-block;
  min-width: 22px;
  padding: 3px 6px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.success {
  background: #198754;
  color: #fff;
}

.muted {
  background: #e9ecef;
  color: #6c757d;
}

.actions-cell {
  white-space: nowrap;
}

.btn {
  font-size: 13px;
  padding: 4px 10px;
  border-radius: 4px;
  border: none;
  margin: 0 2px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-edit {
  background-color: #0d6efd;
  color: white;
}

.btn-edit:hover {
  background-color: #0b5ed7;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

.btn-delete:hover {
  background-color: #bb2d3b;
}

.error {
  color: #dc3545;
  margin-top: 10px;
}

.permission-table tr:hover {
  background: #f1f3f5;
}
</style>
