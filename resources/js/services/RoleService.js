import api from '@/api/axios';

export const RoleService = {
    /**
     * ទាញយកបញ្ជី Role ទាំងអស់ (គាំទ្រ Pagination & Search)
     */
    async getAll(page = 1, search = '') {
        try {
            const response = await api.get('/roles', { 
                params: { 
                    page, 
                    search: search || undefined 
                } 
            });
            return response.data; 
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * ✅ បន្ថែម Method នេះចូល (ដែល Index.vue កំពុងត្រូវការ)
     * ប្រើសម្រាប់ទាញយក Role ទាំងអស់មកដាក់ក្នុង Select Box
     */
    async getRolesForSelect() {
        try {
            // យើងហៅទៅកាន់ route /roles តែផ្ញើ per_page ច្រើនដើម្បីឱ្យបានបញ្ជីទាំងអស់
            const response = await api.get('/roles', { 
                params: { per_page: 100 } 
            });
            
            // ប្រសិនបើ Backend ប្រើ Resource/Paginate ទិន្នន័យនៅក្នុង .data.data
            // បើមិនប្រើ Paginate ទេ គឺយក .data ធម្មតា
            return response.data.data || response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * ទាញយក Permissions ទាំងអស់
     */
    async getAllPermissions() {
        try {
            const response = await api.get('/permissions');
            const result = response.data.data || response.data;
            return Array.isArray(result) ? result : [];
        } catch (error) {
            this.handleError(error);
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/roles/${id}`);
            return response.data.data || response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    async create(data) {
        try {
            const response = await api.post('/roles', data);
            return response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    async update(id, data) {
        try {
            const response = await api.put(`/roles/${id}`, data);
            return response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    async delete(id) {
        try {
            const response = await api.delete(`/roles/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * មុខងារចាប់កំហុស (Error Handling)
     */
    handleError(error) {
        const message = error.response?.data?.message || 'មានបញ្ហាបច្ចេកទេសកើតឡើង';
        const errors = error.response?.data?.errors || null;
        
        throw { 
            message, 
            errors, 
            status: error.response?.status 
        };
    }
};