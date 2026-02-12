import api from '@/api/axios'

export const ScheduleService = {
    /**
     * Fetch all schedules with pagination and search filters
     */
    async getAll(page = 1, searchQuery = '', perPage = 9) {
        const response = await api.get('/schedules/all', {
            params: { 
                page, 
                per_page: perPage, 
                search: searchQuery 
            }
        })
        return response.data
    },

    /**
     * Update an existing schedule
     */
    async update(id, data) {
        const response = await api.put(`/schedules/${id}`, data)
        return response.data
    },

    /**
     * Delete a schedule by ID
     */
    async delete(id) {
        const response = await api.delete(`/schedules/${id}`)
        return response.data
    }
}