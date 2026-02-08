<template>
    <div>
        <h2>Schedules</h2>
        <button @click="load">Refresh</button>
        <ul v-if="schedules.length">
            <li v-for="s in schedules" :key="s.id">
                <h3>{{ s.title }}</h3>
                <p>{{ s.description }}</p>
                <small>{{ s.start_at }} → {{ s.end_at }}</small>
                <button @click="remove(s.id)">Delete</button>
            </li>
        </ul>
        <form @submit.prevent="create">
            <input v-model="form.title" placeholder="Title" required />
            <input v-model="form.start_at" type="datetime-local" required />
            <button>Create</button>
        </form>
    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue';
    import api from '../services/api';

    const schedules = ref([]);
    const form = ref({title:'',description:'',start_at:'',end_at:''});

    async function load(){ const res = await api.get('/schedules'); schedules.value = res.data.data || res.data; }
    async function create(){ await api.post('/schedules', form.value); form.value={title:'',description:'',start_at:'',end_at:''}; load(); }
    async function remove(id){ await api.delete(`/schedules/${id}`); load(); }

    onMounted(load);
</script>
