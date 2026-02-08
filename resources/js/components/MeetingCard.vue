<template>
  <div class="meeting-card shadow-sm mb-3">
    <div :class="['time-strip', variant]">
      <div class="fw-bold">{{ time }}</div>
      <div class="tiny">ជាន់ទី បន្ទប់</div>
    </div>
    <div class="info-strip p-2 bg-white flex-grow-1">
      <div class="kh-title">{{ title }}</div>
      <div class="kh-desc text-muted">{{ desc }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps(['time', 'title', 'desc', 'variant'])
</script>

<style scoped>
.meeting-card { display: flex; border-radius: 10px; overflow: hidden; height: 90px; z-index: 10; border: 1px solid #eee; }
.time-strip { width: 130px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.red { background: #f15a42; color: white; }
.yellow { background: #fefcbf; color: #744210; }
.kh-title { font-weight: 700; font-size: 0.8rem; }
.kh-desc { font-size: 0.6rem; }
</style>