import api from '@/api/axios';

export const PermissionService = {
    /**
     * getAll ឱ្យកាន់តែ Dynamic គាំទ្រទាំង page, search, និង per_page
     */
    async getAll(params = {}) {
        try {
            // ប្រើ params ជា Object តែម្តង ដើម្បីងាយស្រួលពង្រីកថ្ងៃក្រោយ
            const { data } = await api.get('/permissions', { params });
            return data; 
        } catch (error) { this.handleError(error); }
    },

    async getById(id) {
        try {
            const { data } = await api.get(`/permissions/${id}`);
            return data.data || data;
        } catch (error) { this.handleError(error); }
    },

    /**
     * ប្រើ Helper ខាងក្រោមដើម្បីសម្អាតឈ្មោះ (Slugify)
     */
    async create(payload) {
        try {
            const data = { ...payload, name: this.formatName(payload.name) };
            const res = await api.post('/permissions', data);
            return res.data;
        } catch (error) { this.handleError(error); }
    },

    async update(id, payload) {
        try {
            const data = { ...payload, name: this.formatName(payload.name) };
            const res = await api.put(`/permissions/${id}`, data);
            return res.data;
        } catch (error) { this.handleError(error); }
    },

    async delete(id) {
        try {
            const { data } = await api.delete(`/permissions/${id}`);
            return data;
        } catch (error) { this.handleError(error); }
    },

    // --- Helpers ---
    
    formatName(name) {
        return name ? name.trim().toLowerCase().replace(/\s+/g, '_') : name;
    },

    handleError(error) {
        throw { 
            message: error.response?.data?.message || 'មានបញ្ហាបច្ចេកទេស', 
            errors: error.response?.data?.errors || null, 
            status: error.response?.status 
        };
    }
};