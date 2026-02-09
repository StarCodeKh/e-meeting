<template>
    <transition name="toast-fade">
        <div v-if="alertStore.message" :class="['alert-toast', alertStore.type]" role="alert">
            <div class="alert-icon-wrapper">
                <i v-if="alertStore.type === 'success'" class="bi bi-check-circle-fill"></i>
                <i v-else-if="alertStore.type === 'error'" class="bi bi-exclamation-triangle-fill"></i>
                <i v-else class="bi bi-info-circle-fill"></i>
            </div>

            <div class="alert-content">
                <h6 class="alert-title khmer-font">{{ titles[alertStore.type] }}</h6>
                <p class="alert-message khmer-font">{{ alertStore.message }}</p>
            </div>

            <button class="alert-close" @click="closeAlert">
                <i class="bi bi-x"></i>
            </button>
            
            <div class="alert-progress" :style="{ backgroundColor: progressColors[alertStore.type] }"></div>
        </div>
    </transition>
</template>

<script setup>
    import { alertStore } from '@/stores/alert'
    const titles = {
        success: 'ជោគជ័យ',
        error: 'មានបញ្ហា',
        info: 'ព័ត៌មាន',
    }

    const progressColors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
    }

    function closeAlert() {
        alertStore.message = ''
        alertStore.type = ''
    }
</script>

<style scoped>
    /* Use @ to start from resources/js/ */
    @import "@/css/app-alert.css";
</style>