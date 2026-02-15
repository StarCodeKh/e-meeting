<template>
    <div class="theme-selection p-4" :style="dynamicStyles">
        
        <div class="accent-color-section mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="khmer-font fw-bold mb-0 text-muted small text-uppercase">ពណ៌ចម្បង</h6>
                
                <button @click="handleReset" 
                        class="btn btn-sm btn-outline-secondary khmer-font py-1 px-3 rounded-pill shadow-sm transition-all"
                        style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> កំណត់ឡើងវិញ
                </button>
            </div>

            <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-3 border">
                <div v-for="color in allColorOptions" :key="color" 
                     class="color-bubble shadow-sm" 
                     :style="{ backgroundColor: color }" 
                     :class="{ 'selected-color': customColor === color }" 
                     @click="customColor = color">
                    <i v-if="customColor === color" class="bi bi-check-lg text-white"></i>
                </div>
            </div>
        </div>

        <div class="priority-section border-top pt-4 mt-4">
            <h6 class="khmer-font fw-bold mb-3 text-muted small text-uppercase">កម្រិតអាទិភាព</h6>
            <div class="row g-3">
                <div v-for="p in priorities" :key="p.slug" class="col-12 col-md-4">
                    <div class="priority-card p-3 rounded-3 border-2 shadow-sm h-100 position-relative" 
                         :class="{ 'priority-active': activePriority === p.slug }" 
                         :style="getPriorityStyle(p)" 
                         @click="handlePriorityUpdate(p)">
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge rounded-pill px-3 shadow-sm transition-all" 
                                  :style="{ 
                                      backgroundColor: activePriority === p.slug ? 'var(--accent-color)' : p.color_hex, 
                                      color: 'white' 
                                  }">
                                {{ p.name }}
                            </span>
                            
                            <i :class="getPriorityIcon(p.slug)" class="fs-5 transition-all" 
                               :style="{ color: activePriority === p.slug ? 'var(--accent-color)' : p.color_hex }">
                            </i>
                        </div>
                        
                        <p class="small mb-0 text-muted khmer-font">កម្រិត {{ p.name }}</p>

                        <div class="mt-3" style="height: 4px; background: rgba(0,0,0,0.05); border-radius: 10px;">
                            <div class="h-100 transition-all" 
                                 :style="{ 
                                     width: activePriority === p.slug ? '100%' : '30%', 
                                     backgroundColor: activePriority === p.slug ? 'var(--accent-color)' : p.color_hex 
                                 }">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="display-container border-top pt-5 mt-5">
            <h6 class="khmer-font fw-bold mb-4 text-muted small text-uppercase">ការបង្ហាញគ្រប់ប្រភេទការងារ</h6>
            <div class="row g-4">
                <div v-for="type in scheduleTypes" :key="type.slug" class="col-12 col-md-4">
                    <div class="preview-card shadow-lg h-100" 
                         :class="{ 'is-selected': currentType === type.slug }" 
                         :style="getTabStyle(type)" 
                         @click="handleTypeUpdate(type)">
                        <div class="d-flex justify-content-between align-items-center text-white">
                            <div>
                                <h4 class="khmer-font mb-1">{{ type.name }}</h4>
                                <p class="opacity-75 small mb-0 font-italic">{{ type.slug }}</p>
                            </div>
                            <i :class="type.icon" class="display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, watch } from 'vue'
    import { 
        getScheduleFormOptions, 
        updatePriorityColor, 
        updateTypeColor 
    } from '@/services/ScheduleTypes'
    import Swal from 'sweetalert2'

    // --- Configurations ---
    const DEFAULT_ACCENT = '#4285f4'

    // --- States ---
    const theme = ref(localStorage.getItem('app-theme') || 'light')
    const customColor = ref(localStorage.getItem('app-custom-accent') || DEFAULT_ACCENT)
    const currentType = ref(localStorage.getItem('selected-type-slug') || '')
    const activePriority = ref('')
    const scheduleTypes = ref([])
    const priorities = ref([])
    const isUpdating = ref(false)

    // --- Fetch Data ---
    onMounted(async () => {
        const data = await getScheduleFormOptions();
        if (data) {
            scheduleTypes.value = data.types || [];
            priorities.value = data.priorities || [];
            if (!currentType.value && scheduleTypes.value.length) {
                currentType.value = scheduleTypes.value[0].slug;
            }
            if (priorities.value.length) activePriority.value = priorities.value[0].slug;
        }
    })

    // --- Computed Styles ---
    const dynamicStyles = computed(() => {
        return { '--accent-color': customColor.value };
    });

    const allColorOptions = computed(() => {
        const dbColors = scheduleTypes.value.map(t => t.color_hex);
        const presetColors = ['#4285f4', '#34a853', '#fbbc05', '#ea4335', '#673ab7', '#e91e63'];
        return [...new Set([...dbColors, ...presetColors])];
    });

    // --- Helpers ---
    const showToast = (icon, title) => {
        Swal.fire({
            icon: icon,
            title: title,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            customClass: { popup: 'khmer-font' }
        });
    }

    // --- Actions ---

    // ១. កំណត់ឡើងវិញ (Reset)
    const handleReset = () => {
        Swal.fire({
            title: 'កំណត់ឡើងវិញ?',
            text: "ពណ៌ចម្បងនឹងត្រឡប់ទៅរកលំនាំដើម!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4285f4',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> យល់ព្រម',
            cancelButtonText: '<i class="bi bi-x-lg me-1"></i> បោះបង់',
            customClass: { 
                popup: 'khmer-font',
                confirmButton: 'px-4',
                cancelButton: 'px-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                customColor.value = DEFAULT_ACCENT;
                showToast('success', 'បានកំណត់ឡើងវិញ');
            }
        });
    }

    // ២. Update Priority
    const handlePriorityUpdate = async (p) => {
        activePriority.value = p.slug;
        const originalColor = p.color_hex;
        const newColor = customColor.value;
        
        try {
            isUpdating.value = true;
            await updatePriorityColor(p.slug, newColor);
            p.color_hex = newColor;
            showToast('success', 'រក្សាទុកកម្រិតអាទិភាពជោគជ័យ');
        } catch (error) {
            p.color_hex = originalColor;
            showToast('error', 'ការរក្សាទុកបរាជ័យ');
        } finally {
            isUpdating.value = false;
        }
    };

    // ៣. Update Schedule Type
    const handleTypeUpdate = async (type) => {
        currentType.value = type.slug;
        const originalColor = type.color_hex;
        const newColor = customColor.value;

        try {
            isUpdating.value = true;
            await updateTypeColor(type.slug, newColor);
            type.color_hex = newColor;
            showToast('success', 'បច្ចុប្បន្នភាពប្រភេទកិច្ចការជោគជ័យ');
        } catch (error) {
            type.color_hex = originalColor;
            showToast('error', 'មិនអាចប្តូរពណ៌ប្រភេទកិច្ចការបានទេ');
        } finally {
            isUpdating.value = false;
        }
    };

    // --- UI Styles Helpers ---
    const getPriorityStyle = (p) => {
        const isSelected = activePriority.value === p.slug;
        return {
            borderColor: isSelected ? 'var(--accent-color)' : (theme.value === 'dark' ? '#333' : '#eee'),
            borderTop: isSelected ? `6px solid var(--accent-color)` : `6px solid ${p.color_hex}`,
            backgroundColor: theme.value === 'dark' ? '#1e1e1e' : '#ffffff',
            transform: isSelected ? 'scale(1.02)' : 'scale(1)',
            transition: 'all 0.3s ease',
            cursor: 'pointer'
        };
    };

    const getTabStyle = (type) => {
        const isActive = currentType.value === type.slug;
        const color = isActive ? customColor.value : type.color_hex;
        return {
            background: `linear-gradient(135deg, ${color}, ${color}cc)`,
            opacity: isActive ? '1' : '0.6',
            transform: isActive ? 'scale(1.02)' : 'scale(1)',
            transition: 'all 0.4s ease',
            cursor: 'pointer'
        };
    };

    const getPriorityIcon = (slug) => {
        const icons = { red: 'bi bi-fire', yellow: 'bi bi-lightning-fill', green: 'bi bi-info-circle-fill' };
        return icons[slug] || 'bi bi-dot';
    };

    // --- Watchers ---
    watch([theme, currentType, customColor], () => {
        localStorage.setItem('app-theme', theme.value);
        localStorage.setItem('selected-type-slug', currentType.value);
        localStorage.setItem('app-custom-accent', customColor.value);
        document.documentElement.setAttribute('data-bs-theme', theme.value);
    });
</script>

<style scoped>
    .khmer-font { font-family: 'Kantumruy Pro', sans-serif; }

    .priority-card { border-width: 2px !important; }
    .priority-active { box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; z-index: 2; }

    .color-bubble {
        width: 35px; height: 35px; border-radius: 10px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: 0.2s;
    }
    .selected-color { box-shadow: 0 0 0 2px white, 0 0 0 5px var(--accent-color); transform: scale(1.1); }

    .preview-card { padding: 30px; border-radius: 25px; transition: 0.4s; }
    .is-selected { border: 2px solid white; box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important; }

    .transition-all { transition: all 0.4s ease; }

    .transition-all {
    transition: all 0.3s ease;
}
</style>