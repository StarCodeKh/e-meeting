<template>
  <div class="rbac-wrapper">

    <!-- LEFT PANEL: Roles list -->
    <aside class="roles-panel">
      <h2>Roles & Permissions</h2>

      <button class="btn-add" @click="goCreateRole">
        + Add Role
      </button>

      <ul class="roles">
        <li
          v-for="role in roles"
          :key="role.id"
          :class="{ active: role.id === activeRoleId }"
          @click="selectRole(role)"
          tabindex="0"
          @keyup.enter="selectRole(role)"
        >
          {{ role.name || role.title || 'No Name' }}
        </li>
      </ul>

      <!-- Debug: uncomment to see raw roles data -->
      <!-- <pre>{{ JSON.stringify(roles, null, 2) }}</pre> -->
    </aside>

    <!-- RIGHT PANEL: Permissions for selected role -->
    <section class="permissions-panel" v-if="activeRole">

      <h3>Module Access</h3>
      <div class="module-access">
        <div v-for="m in modules" :key="m.key" class="module-row">
          <span class="module-label">{{ m.label }}</span>

          <label class="switch">
            <input
              type="checkbox"
              :checked="hasModule(m.key)"
              @change="toggleModule(m.key)"
            />
            <span class="slider round"></span>
          </label>
        </div>
      </div>

      <h3 class="mt">Module Permissions</h3>
      <table class="permission-table">
        <thead>
          <tr>
            <th>Module</th>
            <th v-for="a in actions" :key="a" class="action-header">
              {{ a }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in modules" :key="m.key">
            <td class="module-label">{{ m.label }}</td>
            <td v-for="a in actions" :key="a" class="action-cell">
              <input
                type="checkbox"
                :checked="hasPermission(m.key, a)"
                @change="togglePermission(m.key, a)"
              />
            </td>
          </tr>
        </tbody>
      </table>

    </section>

    <!-- If no role selected -->
    <section v-else class="empty">
      Select a role to manage permissions
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

const router = useRouter()

const roles = ref([])
const permissions = ref([])
const activeRole = ref(null)
const activeRoleId = ref(null)

const modules = ref([])  // now reactive and empty initially
const actions = ref([])  // reactive and empty initially

function goCreateRole() {
  router.push({ name: 'roles.create' })
}

async function fetchRoles() {
  try {
    const res = await api.get('/roles')
    if (res.data && Array.isArray(res.data.data)) {
      roles.value = res.data.data
    } else {
      roles.value = []
      console.warn('Roles data missing or invalid from API')
    }
  } catch (error) {
    console.error('Error fetching roles:', error)
    roles.value = []
  }
}

async function fetchModulesActions() {
  try {
    const res = await api.get('/modules-actions')
    if (res.data) {
      modules.value = res.data.modules || []
      actions.value = res.data.actions || []
    }
  } catch (error) {
    console.error('Failed to load modules/actions:', error)
    modules.value = []
    actions.value = []
  }
}

async function selectRole(role) {
  activeRole.value = role
  activeRoleId.value = role.id

  try {
    const res = await api.get(`/roles/${role.id}/permissions`)
    if (res.data && Array.isArray(res.data.data)) {
      permissions.value = res.data.data
    } else {
      permissions.value = []
      console.warn('Permissions data missing or invalid from API')
    }
  } catch (error) {
    console.error('Error fetching permissions:', error)
    permissions.value = []
  }
}

/* Utility keys and checks */
function key(module, action) {
  return `${module}.${action}`
}

function hasPermission(module, action) {
  return permissions.value.includes(key(module, action))
}

function hasModule(module) {
  return permissions.value.some(p => p.startsWith(`${module}.`))
}

/* Toggle permissions and modules */
async function togglePermission(module, action) {
  try {
    await api.post(`/roles/${activeRoleId.value}/permissions/toggle`, {
      permission: key(module, action)
    })
    await selectRole(activeRole.value)
  } catch (error) {
    console.error('Error toggling permission:', error)
  }
}

async function toggleModule(module) {
  try {
    await api.post(`/roles/${activeRoleId.value}/modules/toggle`, {
      module
    })
    await selectRole(activeRole.value)
  } catch (error) {
    console.error('Error toggling module:', error)
  }
}

onMounted(() => {
  fetchRoles()
  fetchModulesActions()  // fetch modules and actions when component mounts
})
</script>


<style scoped>
/* Container layout */
.rbac-wrapper {
  display: flex;
  gap: 24px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  min-height: 480px;
  color: #333;
}

/* LEFT PANEL: Roles */
.roles-panel {
  width: 260px;
  background: #f9fafb;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 0 10px rgb(0 0 0 / 0.05);
  display: flex;
  flex-direction: column;
  user-select: none;
}

.roles-panel h2 {
  margin: 0 0 20px 0;
  font-weight: 700;
  font-size: 22px;
  color: #1a1a1a;
}

.btn-add {
  background-color: #0d6efd;
  border: none;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  margin-bottom: 20px;
  transition: background-color 0.3s ease;
}

.btn-add:hover,
.btn-add:focus {
  background-color: #0b5ed7;
  outline: none;
}

.roles {
  list-style: none;
  padding: 0;
  margin: 0;
  overflow-y: auto;
  max-height: 350px;
  border-top: 1px solid #ddd;
}

.roles li {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
  cursor: pointer;
  border-radius: 6px;
  transition: background-color 0.2s ease;
  font-weight: 500;
}

.roles li:hover,
.roles li:focus {
  background-color: #e9f0ff;
  outline: none;
}

.roles li.active {
  background-color: #0d6efd;
  color: white;
  font-weight: 700;
}

/* RIGHT PANEL: Permissions */
.permissions-panel {
  flex: 1;
  background: #fff;
  border-radius: 8px;
  padding: 20px 25px;
  box-shadow: 0 0 12px rgb(0 0 0 / 0.07);
  display: flex;
  flex-direction: column;
}

.permissions-panel h3 {
  margin: 0 0 12px 0;
  font-weight: 600;
  font-size: 20px;
  color: #222;
}

.mt {
  margin-top: 30px;
}

/* MODULE ACCESS */
.module-access {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  margin-bottom: 30px;
}

.module-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1 0 160px;
}

.module-label {
  font-weight: 600;
  font-size: 15px;
}

/* Toggle switch styles */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
  cursor: pointer;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  border-radius: 24px;
  transition: 0.3s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: 0.3s;
}

input:checked + .slider {
  background-color: #0d6efd;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

.slider.round {
  border-radius: 24px;
}

/* PERMISSIONS TABLE */
.permission-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  table-layout: fixed;
  box-shadow: 0 0 8px rgb(0 0 0 / 0.04);
  border-radius: 6px;
  overflow: hidden;
}

.permission-table thead {
  background: #f1f3f5;
}

.permission-table th,
.permission-table td {
  border: 1px solid #dee2e6;
  padding: 12px 10px;
  text-align: center;
  vertical-align: middle;
  word-wrap: break-word;
}

.permission-table th {
  font-weight: 600;
  color: #555;
  text-transform: capitalize;
}

.module-label {
  font-weight: 600;
  text-align: left;
  padding-left: 18px;
  color: #222;
}

.action-header {
  width: 60px;
}

.action-cell input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

/* EMPTY STATE */
.empty {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 18px;
  color: #666;
  font-style: italic;
  user-select: none;
}

/* Responsive */
@media (max-width: 768px) {
  .rbac-wrapper {
    flex-direction: column;
  }

  .roles-panel {
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 20px;
  }

  .permissions-panel {
    padding: 15px;
  }

  .module-access {
    gap: 12px;
  }

  .module-row {
    flex: 1 0 140px;
  }
}
</style>
