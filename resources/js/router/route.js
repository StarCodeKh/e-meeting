export default [
    /* --- Login --- */
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/auth/Login.vue'),
        meta: { requiresAuth: false, title: 'ចូលប្រើប្រាស់ប្រព័ន្ធ' }
    },

    /* --- Calendar --- */
    {
        path: '/calendar',
        name: 'calendar',
        component: () => import('@/pages/CalendarWidget.vue'),
        meta: { requiresAuth: false, title: 'ប្រតិទិនការងារ' }
    },
    {
        path: '/meeting-monitor',
        name: 'meeting-monitor',
        component: () => import('@/pages/MeetingMonitor.vue'),
        meta: { requiresAuth: false, title: 'ម៉ូនីទ័រការប្រជុំ' }
    },

    /* --- SCHEDULE MANAGEMENT --- */
    {
        path: '/schedules/all',
        name: 'schedules.index',
        component: () => import('@/pages/forms/ScheduleList.vue'),
        meta: { requiresAuth: true, title: 'បញ្ជីកាលវិភាគប្រជុំ' }
    },
    /* --- SETTINGS --- */
    {
        path: '/settings',
        name: 'settings',
        component: () => import('@/pages/settings/SettingsView.vue'),
        meta: { requiresAuth: true, title: 'ការកំណត់' }
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('@/pages/users/Index.vue'),
        meta: { requiresAuth: true, title: 'គ្រប់គ្រងអ្នកប្រើប្រាស់' }
    }
]