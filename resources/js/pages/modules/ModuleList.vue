<template>
  <div class="modules-list">
    <h1>Modules List</h1>
    <button class="btn-create" @click="goCreate">+ Create Module</button>

    <table class="modules-table" v-if="modules.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Key Name</th>
          <th>Label</th>
          <th>Description</th>
          <th>Sort Order</th>
          <th>Active</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="module in modules" :key="module.id">
          <td>{{ module.id }}</td>
          <td>{{ module.key_name }}</td>
          <td>{{ module.label }}</td>
          <td>{{ module.description || '-' }}</td>
          <td>{{ module.sort_order }}</td>
          <td>
            <span :class="module.is_active ? 'status-active' : 'status-inactive'">
              {{ module.is_active ? 'Yes' : 'No' }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>No modules found.</p>
  </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import { useRouter } from 'vue-router'
    import api from '@/api/axios'
    const modules = ref([])
    const router = useRouter()

    const fetchModules = async () => {
    try {
        const res = await api.get('/modules')  // use axios.get
        modules.value = res.data.data || []
    } catch (err) {
        console.error('Failed to load modules:', err)
    }
    }

    const goCreate = () => {
    router.push({ name: 'modules.create' })
    }

    onMounted(() => {
    fetchModules()
    })
</script>

<style scoped>
.modules-list {
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

.modules-table {
  width: 100%;
  border-collapse: collapse;
  box-shadow: 0 0 8px rgb(0 0 0 / 0.1);
  border-radius: 5px;
  overflow: hidden;
}

.modules-table th,
.modules-table td {
  border: 1px solid #ddd;
  padding: 10px 12px;
  text-align: left;
}

.modules-table th {
  background-color: #f1f3f5;
  font-weight: 600;
  color: #555;
}

.status-active {
  color: green;
  font-weight: 600;
}

.status-inactive {
  color: red;
  font-weight: 600;
}
</style>
