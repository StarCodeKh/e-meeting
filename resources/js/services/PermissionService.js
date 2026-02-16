import api from '@/api/axios';

export const PermissionService = {
    /**
     * ទាញយកបញ្ជី Permission ទាំងអស់ (គាំទ្រ Pagination & Search)
     */
    async getAll(page = 1, search = '') {
        try {
            const response = await api.get('/permissions', { 
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
     * ទាញយក Permission មួយតាម ID
     */
    async getById(id) {
        try {
            const response = await api.get(`/permissions/${id}`);
            return response.data.data || response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * បង្កើត Permission ថ្មី
     */
    async create(data) {
        try {
            if (data.name) {
                data.name = data.name.trim().toLowerCase().replace(/\s+/g, '_');
            }
            const response = await api.post('/permissions', data);
            return response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * ធ្វើបច្ចុប្បន្នភាព Permission
     */
    async update(id, data) {
        try {
            if (data.name) {
                data.name = data.name.trim().toLowerCase().replace(/\s+/g, '_');
            }
            const response = await api.put(`/permissions/${id}`, data);
            return response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * លុប Permission
     */
    async delete(id) {
        try {
            const response = await api.delete(`/permissions/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error);
        }
    },

    /**
     * មុខងារចាប់កំហុស (Error Handling) - ដូច RoleService
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