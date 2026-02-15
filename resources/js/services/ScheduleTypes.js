// src/services/ScheduleTypes.js
import api from '@/api/axios';

export const getScheduleFormOptions = async () => {
    try {
        const response = await api.get('/schedule-form-options');
        return response.data.data; 
    } catch (error) {
        console.error("Error fetching schedule form options:", error);
        throw error;
    }
};