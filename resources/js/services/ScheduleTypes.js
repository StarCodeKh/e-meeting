// src/services/ScheduleTypes.js
import api from '@/api/axios';

/**
 * ១. ទាញទិន្នន័យដំបូង (Fetch All Options)
 */
export const getScheduleFormOptions = async () => {
    try {
        const response = await api.get('/schedule-form-options');
        return response.data.data; 
    } catch (error) {
        console.error("Error fetching schedule form options:", error);
        throw error;
    }
};

/**
 * ២. Update ពណ៌របស់ Priority
 */
export const updatePriorityColor = async (slug, colorHex) => {
    try {
        const response = await api.post('/priorities/update', {
            slug: slug,
            color_hex: colorHex
        });
        return response.data;
    } catch (error) {
        console.error("Error updating priority color:", error);
        throw error;
    }
};

/**
 * ៣. Update ពណ៌របស់ Schedule Type (កិច្ចប្រជុំ, ការណាត់, ការងារ)
 * បងបន្ថែមមួយនេះទៀត ដើម្បីឱ្យស៊ីគ្នាជាមួយ Backend Controller ថ្មី
 */
export const updateTypeColor = async (slug, colorHex) => {
    try {
        const response = await api.post('/schedule-types/update', {
            slug: slug,
            color_hex: colorHex
        });
        return response.data;
    } catch (error) {
        console.error("Error updating schedule type color:", error);
        throw error;
    }
};