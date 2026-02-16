import { defineStore } from 'pinia';
import AuthAPI from '@/api/auth';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(payload) {
            const res = await AuthAPI.login(payload);
            this.token = res.data.token;
            localStorage.setItem('token', this.token);
        },
        async fetchUser() {
            if (!this.token || this.user) return;
            const res = await AuthAPI.me();
            this.user = res.data;
        },
        logout() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
        },
    },
});
