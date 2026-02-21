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
     * ទាញយកការកំណត់ (Get Settings)
     */
    getNotificationSettings() {
        return apiClient.get('/notification-settings');
    },

    /**
     * រក្សាទុកការកំណត់ (Update Settings)
     * បន្ថែមមុខងារនេះ ដើម្បីឱ្យ Service ពេញលេញ
     */
    updateNotificationSettings(data) {
        return apiClient.post('/notification-settings', data);
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
     * ទាញយករបាយការណ៍ (Excel/PDF)
     */
    getExportUrl(type, filters) {
        const token = localStorage.getItem('token');
        
        const params = { ...filters, token };
        Object.keys(params).forEach(key => {
            if (params[key] === null || params[key] === undefined || params[key] === '') {
                delete params[key];
            }
        });

        const query = new URLSearchParams(params).toString();
        
        return `${apiClient.defaults.baseURL}/analytics/export/${type}?${query}`;
    }
};