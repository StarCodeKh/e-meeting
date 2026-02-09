<template>
  <div class="theme-selection">
    <h6 class="khmer-font fw-bold mb-3">ជ្រើសរើសស្ទីលបង្ហាញ (Theme)</h6>
    
    <div class="row g-3">
      <div class="col-6 col-md-4">
        <div 
          class="theme-card shadow-sm p-3 rounded-4 border-2" 
          :class="{ 'active-theme': theme === 'light' }"
          @click="theme = 'light'"
        >
          <div class="theme-preview light-bg mb-2 rounded-3"></div>
          <p class="text-center mb-0 fw-bold small">Light Mode</p>
        </div>
      </div>

      <div class="col-6 col-md-4">
        <div 
          class="theme-card shadow-sm p-3 rounded-4 border-2 bg-dark text-white" 
          :class="{ 'active-theme': theme === 'dark' }"
          @click="theme = 'dark'"
        >
          <div class="theme-preview dark-bg mb-2 rounded-3"></div>
          <p class="text-center mb-0 fw-bold small">Dark Mode</p>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <h6 class="khmer-font fw-bold mb-3">ពណ៌ចម្បង (Accent Color)</h6>
      <div class="d-flex gap-3">
        <div v-for="color in colors" :key="color" 
             class="color-bubble" 
             :style="{ backgroundColor: color }"
             :class="{ 'selected-color': accentColor === color }"
             @click="accentColor = color">
          <i v-if="accentColor === color" class="bi bi-check text-white"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
    import { ref } from 'vue'

    const theme = ref('light')
    const accentColor = ref('#4285f4')
    const colors = ['#4285f4', '#e54d42', '#34a853', '#fbbc05', '#673ab7']
</script>

<style scoped>
    .theme-card { cursor: pointer; border: 2px solid transparent; transition: 0.2s; }
    .active-theme { border-color: #4285f4 !important; }
    .theme-preview { height: 60px; width: 100%; border: 1px solid #eee; }
    .light-bg { background: #f8f9fa; }
    .dark-bg { background: #212529; }

    .color-bubble {
    width: 35px; height: 35px; border-radius: 50%;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: transform 0.2s;
    }
    .selected-color { transform: scale(1.2); border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
</style>