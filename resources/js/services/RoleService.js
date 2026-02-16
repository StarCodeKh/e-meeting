import api from '@/api/axios';

export const RoleService = {
    /**
     * ទាញយកបញ្ជី Role ទាំងអស់ (គាំទ្រ Pagination & Search)
     */
    async getAll(page = 1, search = '') {
        try {
            // បញ្ជូន params ទៅកាន់ Backend
            const response = await api.get('/roles', { 
                params: { 
                    page, 
                    search: search || undefined 
                } 
            });
            // ប្រសិនបើប្រើ Laravel Paginate វានឹងនៅ response.data
            return response.data; 
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * ទាញយក Permissions ទាំងអស់សម្រាប់បង្ហាញក្នុង Checkbox
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