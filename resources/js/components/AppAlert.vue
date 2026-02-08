<template>
  <transition name="fade-slide">
    <div v-if="alertStore.message" :class="['alert-box', alertStore.type]" role="alert" aria-live="assertive" aria-atomic="true">
      <span class="icon" aria-hidden="true">{{ icons[alertStore.type] || 'ℹ️' }}</span>
      <div class="message">{{ alertStore.message }}</div>
      <button class="close-btn" @click="closeAlert" aria-label="Close alert">&times;</button>
    </div>
  </transition>
</template>

<script setup>
import { alertStore } from '@/stores/alert'

const icons = {
  success: '✅',
  error: '⚠️',
  info: 'ℹ️',
}

function closeAlert() {
  alertStore.message = ''
  alertStore.type = ''
}
</script>

<style scoped>
.alert-box {
  position: fixed;
  top: 1.25rem; /* 20px */
  right: 1.25rem;
  display: flex;
  align-items: center;
  padding: 1rem 1.25rem;
  border-radius: 0.5rem;
  color: white;
  font-size: 1rem;
  z-index: 9999;
  min-width: 280px;
  max-width: 350px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  user-select: none;
  font-weight: 600;
  line-height: 1.3;
}

.icon {
  margin-right: 0.75rem;
  font-size: 1.4rem;
  flex-shrink: 0;
  user-select: none;
}

.message {
  flex-grow: 1;
  user-select: text;
  word-break: break-word;
}

.close-btn {
  background: transparent;
  border: none;
  color: inherit;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0 0.25rem;
  line-height: 1;
  user-select: none;
  transition: color 0.25s ease;
}

.close-btn:hover,
.close-btn:focus {
  color: rgba(255, 255, 255, 0.7);
  outline: none;
}

/* Colors */
.success {
  background-color: rgba(40, 167, 69, 0.25); /* green with 25% opacity */
  border: 1.5px solid rgba(40, 167, 69, 0.75); /* green border 75% opacity */
  border-radius: 0.375rem; /* about 6px, same as rounded-2 */
  box-shadow: none; /* remove shadow if you want */
  color: #155724; /* darker green text */
}

.error {
  background-color: rgba(220, 53, 69, 0.25); /* red with 25% opacity */
  border: 1.5px solid rgba(220, 53, 69, 0.75); /* red border 75% opacity */
  border-radius: 0.375rem;
  box-shadow: none;
  color: #721c24; /* darker red text */
}

.info {
  background-color: rgba(23, 162, 184, 0.25); /* teal/light blue with 25% opacity */
  border: 1.5px solid rgba(23, 162, 184, 0.75);
  border-radius: 0.375rem;
  box-shadow: none;
  color: #0c5460; /* darker info text */
}

/* Fade + Slide Down animation */
@keyframes fadeSlideDown {
  0% {
    opacity: 0;
    transform: translateY(-12px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}
</style>
