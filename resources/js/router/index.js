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

    const isTokenExpired = (token) => {
        if (!token) return true;
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));

            const { exp } = JSON.parse(jsonPayload);
            return (Date.now() >= exp * 1000);
        } catch (e) {
            return true;
        }
    };

    if (requiresAuth && (!token || isTokenExpired(token))) {
        localStorage.removeItem('token');
        next({ name: 'login' })
    } else if (to.name === 'login' && token && !isTokenExpired(token)) {
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