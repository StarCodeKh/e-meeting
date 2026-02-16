import api from '@/api/axios';

export const UserService = {
    /**
     * ទាញយកព័ត៌មាន Profile របស់ User ដែលកំពុង Login (Current User)
     * ហៅទៅកាន់ Route: GET /api/user-profile
    */
    async getProfile() {
        try {
            const response = await api.get('/user-profile');
            // Laravel Resource ជាទូទៅបោះទិន្នន័យក្នុង response.data.data
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    /**
     * កែសម្រួលព័ត៌មាន Profile ផ្ទាល់ខ្លួន
     * ប្រើ POST ជាមួយ _method=PUT សម្រាប់រុញ FormData (រូបភាព) ក្នុង Laravel
    */
    async updateProfile(formData) {
        try {
            if (formData instanceof FormData && !formData.has('_method')) {
                formData.append('_method', 'PUT');
            }
            const response = await api.post('/user-profile', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    async getAll(page = 1, search = '', filters = {}) {
        try {
            const params = {
                page,
                search: search || undefined,
                ...filters
            };
            const response = await api.get('/users', { params });
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    async getById(id) {
        try {
            const response = await api.get(`/users/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    async create(formData) {
        try {
            const response = await api.post('/users', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    async update(id, formData) {
        try {
            if (formData instanceof FormData && !formData.has('_method')) {
                formData.append('_method', 'PUT');
            }
            const response = await api.post(`/users/${id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    async delete(id) {
        try {
            const response = await api.delete(`/users/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    },

    /**
     * Centralized Error Handler
    */
    handleError(error) {
        const message = error.response?.data?.message || 'មានបញ្ហាបច្ចេកទេសកើតឡើង';
        const errors = error.response?.data?.errors || null;
        
        console.error(`[User Service Error]: ${message}`, errors);
        throw { message, errors, status: error.response?.status };
    }
};