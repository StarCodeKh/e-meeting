<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        <div class="row g-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="khmer-font fw-bold text-dark mb-1">គ្រប់គ្រងអ្នកប្រើប្រាស់</h4>
                    <p class="text-muted small mb-0">គ្រប់គ្រងសិទ្ធិ និងព័ត៌មានគណនីបុគ្គលិក</p>
                </div>
                <button class="btn btn-primary khmer-font px-4 rounded-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>បន្ថែមថ្មី</span>
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-3 p-lg-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="search-box position-relative">
                                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input v-model="searchQuery" type="text" class="form-control ps-5 rounded-3 border-light-subtle" placeholder="ស្វែងរកតាមឈ្មោះ ឬអីមែល...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle shadow-sm bg-white rounded-4 overflow-hidden">
                        <thead class="bg-light khmer-font">
                            <tr>
                                <th class="ps-4">កម្មវិធី / ប្រភេទ</th>
                                <th>ម៉ោង និងកាលបរិច្ឆេទ</th>
                                <th>ទីតាំង / បន្ទប់</th>
                                <th>អ្នកចូលរួម (Participants)</th>
                                <th class="text-center">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in schedules" :key="item.id">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div :style="{ backgroundColor: getPriorityColor(item.color_id) }" 
                                            style="width: 5px; height: 35px; border-radius: 10px;" class="me-3"></div>
                                        <div>
                                            <div class="fw-bold khmer-font text-dark">{{ item.title }}</div>
                                            <span class="badge rounded-pill bg-light text-muted border small">{{ item.type }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> {{ item.date }}</div>
                                    <div class="fw-bold text-primary"><i class="bi bi-clock me-1"></i> {{ item.start_time }} - {{ item.end_time }}</div>
                                </td>
                                <td>
                                    <div class="khmer-font small"><i class="bi bi-geo-alt me-1 text-danger"></i> {{ item.location }}</div>
                                    <div class="text-muted small">បន្ទប់៖ {{ item.room || 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span v-for="(name, index) in item.participants" :key="index" 
                                            class="badge bg-soft-primary text-primary border-primary border-opacity-25 khmer-font fw-normal">
                                            <i class="bi bi-person me-1"></i>{{ name }}
                                        </span>
                                        <span v-if="!item.participants || item.participants.length === 0" class="text-muted small italic">គ្មានអ្នកចូលរួម</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm rounded-3">
                                        <button class="btn btn-white btn-sm" @click="editSchedule(item)"><i class="bi bi-pencil-square text-primary"></i></button>
                                        <button class="btn btn-white btn-sm" @click="deleteSchedule(item.id)"><i class="bi bi-trash3 text-danger"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-0 p-3 d-flex justify-content-between align-items-center border-top">
                    <span class="text-muted small khmer-font">បង្ហាញ {{ paginatedUsers.length }} នាក់</span>
                    <nav v-if="totalPages > 1">
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <button class="page-link shadow-none" @click="currentPage--"><i class="bi bi-chevron-left"></i></button>
                            </li>
                            <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
                                <button class="page-link shadow-none" @click="currentPage = page">{{ page }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                <button class="page-link shadow-none" @click="currentPage++"><i class="bi bi-chevron-right"></i></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>



<script setup>
    import { ref, computed, watch } from 'vue'
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'

    const searchQuery = ref('')
    const currentPage = ref(1)
    const itemsPerPage = 5

    const users = ref([
        { id: 1, name: 'កែវ មុន្នី', email: 'mony.keo@gmail.com', role: 'អ្នកគ្រប់គ្រង', roleClass: 'role-admin', active: true, joinedDate: '១២ មករា ២០២៦', avatar: 'https://ui-avatars.com/api/?name=Mony+Keo&background=random' },
        { id: 2, name: 'សុខ ចាន់ត្រា', email: 'chantra.sok@gmail.com', role: 'បុគ្គលិក', roleClass: 'role-user', active: false, joinedDate: '០៥ កុម្ភៈ ២០២៦', avatar: 'https://ui-avatars.com/api/?name=Chantra+Sok&background=random' },
        { id: 3, name: 'សុខ ចាន់ត្រា', email: 'chantra.sok@gmail.com', role: 'បុគ្គលិក', roleClass: 'role-user', active: false, joinedDate: '០៥ កុម្ភៈ ២០២៦', avatar: 'https://ui-avatars.com/api/?name=Chantra+Sok&background=random' },
        { id: 4, name: 'សុខ ចាន់ត្រា', email: 'chantra.sok@gmail.com', role: 'បុគ្គលិក', roleClass: 'role-user', active: false, joinedDate: '០៥ កុម្ភៈ ២០២៦', avatar: 'https://ui-avatars.com/api/?name=Chantra+Sok&background=random' },
        { id: 5, name: 'សុខ ចាន់ត្រា', email: 'chantra.sok@gmail.com', role: 'បុគ្គលិក', roleClass: 'role-user', active: false, joinedDate: '០៥ កុម្ភៈ ២០២៦', avatar: 'https://ui-avatars.com/api/?name=Chantra+Sok&background=random' },
        { id: 6, name: 'សុខ ចាន់ត្រា', email: 'chantra.sok@gmail.com', role: 'បុគ្គលិក', roleClass: 'role-user', active: false, joinedDate: '០៥ កុម្ភៈ ២០២៦', avatar: 'https://ui-avatars.com/api/?name=Chantra+Sok&background=random' },
        // បន្ថែមទិន្នន័យឱ្យលើសពី ៥ នាក់ដើម្បីតេស្ត Pagination
    ])

    const searchResults = computed(() => {
        return users.value.filter(u => 
            u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    })

    const paginatedUsers = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage
        return searchResults.value.slice(start, start + itemsPerPage)
    })

    const totalPages = computed(() => Math.ceil(searchResults.value.length / itemsPerPage))

    watch(searchQuery, () => currentPage.value = 1)
</script>



<style scoped>

@import url('@/assets/css/style.css');
/* Global Variables for 2026 Palette */
:root {
    --primary-color: #3498db;
    --bg-main: #f8fafc;
    --border-soft: #e2e8f0;
}

.khmer-font {
    font-family: 'Kantumruy Pro', sans-serif !important;
}

.user-page {
    background-color: var(--bg-main);
    min-height: 100vh;
    font-family: 'Kantumruy Pro', sans-serif;
}

.table-responsive {
    width: 100%;
    overflow-x: hidden; 
    display: block;
}

.table {
    width: 100% !important;
    table-layout: fixed;
    word-wrap: break-word;
}

.card {
    border: 1px solid rgba(0, 0, 0, 0.05) !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04) !important;
}

.search-box input {
    background: #f1f5f9;
    border: 1px solid transparent;
    transition: all 0.3s ease;
}

.search-box input:focus {
    background: #fff;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

/* Status Pulsing Dot */
.avatar-wrapper { position: relative; }
.status-pulse {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
}
.status-pulse.active { background-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
.status-pulse.inactive { background-color: #94a3b8; }

.status-pill {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 50px;
}
.status-pill.active { background: #dcfce7; color: #15803d; }
.status-pill.inactive { background: #f1f5f9; color: #475569; }

.badge-role {
    font-size: 0.7rem;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
}
.role-admin { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
.role-user { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

.page-link {
    border: none;
    background: #718096;
    color: #ffffff;
    border-radius: 8px !important;
    font-weight: 600 !important;
    margin: 0 2px;
    padding: 8px 14px;
    transition: 0.2s;
}

.page-item.active .page-link {
    background-color: #3498db !important;
    border-color: #3498db !important;
    color: #ffffff !important;
}
</style>

