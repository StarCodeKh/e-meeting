export default [
  {
    path: '/users',
    name: 'users.index',
    component: () => import('@/pages/users/Index.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users/create',
    name: 'users.create',
    component: () => import('@/pages/users/Create.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users/:id',
    name: 'users.show',
    component: () => import('@/pages/users/Show.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users/:id/edit',
    name: 'users.edit',
    component: () => import('@/pages/users/Edit.vue'),
    meta: { requiresAuth: true }
  }
]
