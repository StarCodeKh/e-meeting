import api from '@/api/axios'

const resource = '/modules'

export default {
    /**
     * ទាញយកបញ្ជី Module ទាំងអស់
     * @param {Object} params - { page, search, per_page }
     */
    async getAll(params = {}) {
        try {
            const { data } = await api.get(resource, { params })
            return data
        } catch (error) {
            this.handleError(error)
        }
    },

    async get(id) {
        try {
            const { data } = await api.get(`${resource}/${id}`)
            return data.data || data
        } catch (error) {
            this.handleError(error)
        }
    },

    async create(payload) {
        try {
            const { data } = await api.post(resource, payload)
            return data
        } catch (error) {
            this.handleError(error)
        }
    },

    async update(id, payload) {
        try {
            const { data } = await api.put(`${resource}/${id}`, payload)
            return data
        } catch (error) {
            this.handleError(error)
        }
    },

    async delete(id) {
        try {
            const { data } = await api.delete(`${resource}/${id}`)
            return data
        } catch (error) {
            this.handleError(error)
        }
    },

    /**
     * មុខងារចាប់កំហុសសម្រាប់ប្រើប្រាស់ជាមួយ SweetAlert ក្នុង UI
     */
    handleError(error) {
        const message = error.response?.data?.message || 'មានបញ្ហាបច្ចេកទេសកើតឡើង'
        const errors = error.response?.data?.errors || null
        
        throw { 
            message, 
            errors, 
            status: error.response?.status 
        }
    }
}