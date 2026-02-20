import axios from 'axios';

const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

apiClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

export const AnalyticsService = {
    /**
     * ទាញយកការកំណត់ការជូនដំណឹង (Notification Settings/Telegram Bot Status)
     */
    getNotificationSettings() {
        return apiClient.get('/notification-settings');
    },

    /**
     * ទាញយកទិន្នន័យសង្ខេប (Summary)
     */
    getSummary(filters) {
        return apiClient.get('/analytics/summary', { params: filters });
    },

    /**
     * ទាញយកទិន្នន័យក្រាហ្វ (Chart Data)
     */
    getChartData(filters) {
        return apiClient.get('/analytics/chart-data', { params: filters });
    },

    /**
     * ទាញយករបាយការណ៍ (Export)
     */
    getExportUrl(type, filters) {
        const token = localStorage.getItem('token');
        const query = new URLSearchParams({
            ...filters,
            type: type,
            token: token
        }).toString();
        
        return `/api/analytics/export?${query}`;
    }
};