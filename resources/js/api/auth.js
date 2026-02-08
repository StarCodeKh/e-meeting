// resources/js/store/auth.js
import { defineStore } from 'pinia';
import api from '@/api/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    setToken(token) {
      this.token = token;
      if (token) localStorage.setItem('token', token);
      else localStorage.removeItem('token');
    },
    setUser(user) {
      this.user = user;
    },
    async login(payload) {
      const res = await api.post('/auth/login', payload);
      const token = res.data?.token || res.data?.access_token || null;
      if (!token) throw new Error('No token returned.');
      this.setToken(token);
      // optionally fetch profile
      try {
        const profile = await api.get('/auth/profile');
        this.setUser(profile.data);
      } catch (_) { /* ignore */ }
      return res;
    },
    async register(payload) {
      const res = await api.post('/auth/register', payload);
      return res;
    },
    logout() {
      this.setToken(null);
      this.user = null;
    }
  }
});
