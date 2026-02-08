export default [
  /* --- Calendar --- */
  {
    path: '/calendar',
    name: 'calendar',
    component: () => import('@/pages/CalendarWidget.vue'),
    meta: { requiresAuth: true, title: 'Calendar' }
  },
  /* --- Calendar --- */
  {
    path: '/meeting-monitor',
    name: 'meeting-monitor',
    component: () => import('@/pages/MeetingMonitor.vue'),
    meta: { requiresAuth: true, title: 'Meeting Monitor' }
  },

  /* --- 🔐 ACCESS CONTROL (Roles & Permissions) --- */
  {
    path: '/permissions',
    name: 'permissions.index',
    component: () => import('@/pages/permissions/PermissionList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/permissions/create',
    name: 'permissions.create',
    component: () => import('@/pages/permissions/CreatePermission.vue'),
    meta: { requiresAuth: true }
  },

  /* ROLES */
  {
    path: '/roles',
    name: 'roles.index',
    component: () => import('@/pages/roles/RoleList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/roles/create',
    name: 'roles.create',
    component: () => import('@/pages/roles/CreateRole.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/roles/edit/:id',
    name: 'roles.edit',
    component: () => import('@/pages/roles/EditRole.vue'),
    meta: { requiresAuth: true }
  },

  /* ROLE & PERMISSION ASSIGNMENT */
  {
    path: '/role-permissions',
    name: 'role.permissions',
    component: () => import('@/pages/rolepermission/RolePermission.vue'),
    meta: { requiresAuth: true }
  },

  /* --- ⚙️ SYSTEM CONFIGURATION (Modules & Actions) --- */
  {
    path: '/modules',
    name: 'modules.index',
    component: () => import('@/pages/modules/ModuleList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/modules/create',
    name: 'modules.create',
    component: () => import('@/pages/modules/CreateModule.vue'),
    meta: { requiresAuth: true }
  },

  /* ACTIONS */
  {
    path: '/actions',
    name: 'actions.index',
    component: () => import('@/pages/actions/ActionList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/actions/create',
    name: 'actions.create',
    component: () => import('@/pages/actions/CreateAction.vue'),
    meta: { requiresAuth: true }
  },
]