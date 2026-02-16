import api from '@/api/axios'

const resource = '/modules'

export default {
    getAll() {
        return api.get(resource)
    },

    get(id) {
        return api.get(`${resource}/${id}`)
    },

    create(payload) {
        return api.post(resource, payload)
    },

    update(id, payload) {
        return api.put(`${resource}/${id}`, payload)
    },

    delete(id) {
        return api.delete(`${resource}/${id}`)
    }
}