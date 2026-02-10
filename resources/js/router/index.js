import { createRouter, createWebHistory } from 'vue-router'

// ១. ទាញយក File ទាំងអស់ក្នុង Folder router
const modules = import.meta.glob('./*.js', { eager: true })

const dynamicRoutes = []

Object.keys(modules).forEach((key) => {
    if (key.includes('index.js')) return

    const mod = modules[key].default
    if (mod) {
        if (Array.isArray(mod)) {
        dynamicRoutes.push(...mod)
        } else {
        dynamicRoutes.push(mod)
        }
    }
})

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: { requiresAuth: true, title: 'Dashboard' }
    },
    
    ...dynamicRoutes,

    { 
        path: '/:pathMatch(.*)*', 
        name: 'not-found',
        redirect: { name: 'dashboard' } 
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 })
})

// ២. Router Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

    if (requiresAuth && !token) {
        next({ name: 'login' })
    } else if (to.name === 'login' && token) {
        next({ name: 'dashboard' })
    } else {
        next()
    }
})

// ៣. ប្ដូរ Title នៅលើ Tab Browser
router.afterEach((to) => {
    document.title = to.meta.title || 'ប្រព័ន្ធប្រជុំអេឡិចត្រូនិក'
})

export default router