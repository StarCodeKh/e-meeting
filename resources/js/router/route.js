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

    /* --- ACCESS CONTROL --- */
    {
        path: '/permissions',
        name: 'permissions.index',
        component: () => import('@/pages/permissions/PermissionList.vue'),
        meta: { requiresAuth: true, title: 'បញ្ជីសិទ្ធិប្រើប្រាស់' }
    },
    {
        path: '/permissions/create',
        name: 'permissions.create',
        component: () => import('@/pages/permissions/CreatePermission.vue'),
        meta: { requiresAuth: true, title: 'បង្កើតសិទ្ធិថ្មី' }
    },

    /* ROLES */
    {
        path: '/roles',
        name: 'roles.index',
        component: () => import('@/pages/roles/RoleList.vue'),
        meta: { requiresAuth: true, title: 'បញ្ជីតួនាទី' }
    },
    {
        path: '/roles/create',
        name: 'roles.create',
        component: () => import('@/pages/roles/CreateRole.vue'),
        meta: { requiresAuth: true, title: 'បង្កើតតួនាទីថ្មី' }
    },
    {
        path: '/roles/edit/:id',
        name: 'roles.edit',
        component: () => import('@/pages/roles/EditRole.vue'),
        meta: { requiresAuth: true, title: 'កែសម្រួលតួនាទី' }
    },

    {
        path: '/role-permissions',
        name: 'role.permissions',
        component: () => import('@/pages/rolepermission/RolePermission.vue'),
        meta: { requiresAuth: true, title: 'កំណត់សិទ្ធិតាមតួនាទី' }
    },

    /* --- SYSTEM CONFIGURATION --- */
    {
        path: '/modules',
        name: 'modules.index',
        component: () => import('@/pages/modules/ModuleList.vue'),
        meta: { requiresAuth: true, title: 'បញ្ជីម៉ូឌុល' }
    },
    {
        path: '/modules/create',
        name: 'modules.create',
        component: () => import('@/pages/modules/CreateModule.vue'),
        meta: { requiresAuth: true, title: 'បង្កើតម៉ូឌុលថ្មី' }
    },

    /* ACTIONS */
    {
        path: '/actions',
        name: 'actions.index',
        component: () => import('@/pages/actions/ActionList.vue'),
        meta: { requiresAuth: true, title: 'បញ្ជីសកម្មភាព' }
    },
    {
        path: '/actions/create',
        name: 'actions.create',
        component: () => import('@/pages/actions/CreateAction.vue'),
        meta: { requiresAuth: true, title: 'បង្កើតសកម្មភាពថ្មី' }
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