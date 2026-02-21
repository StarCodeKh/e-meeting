<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        
        <div class="container-fluid bg-light min-vh-100 p-3 p-lg-4">
            <div class="row g-3 align-items-center mb-4 d-print-none">
                <div class="col-12 col-md-9">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3 d-none d-sm-flex shadow-sm border border-primary border-opacity-10">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <h4 class="khmer-font fw-bold text-dark mb-1">របាយការណ៍ និងការវិភាគ</h4>
                            <div class="d-flex align-items-center">
                                <p class="khmer-font text-muted small mb-0 text-truncate">តាមដានស្ថិតិកិច្ចប្រជុំ និងសកម្មភាពក្នុងប្រព័ន្ធ</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="d-flex justify-content-start justify-content-md-end">
                        <div class="input-group input-group-sm shadow-sm border rounded-3 overflow-hidden bg-white w-md-auto">
                            <span class="input-group-text border-0 bg-light-subtle ps-3">
                                <i class="bi bi-calendar3 text-primary"></i>
                            </span>

                            <DatePicker v-model.range="range" :popover="{ visibility: 'click', placement: 'bottom-end', positionFixed: true }" color="blue">
                                <template #default="{ inputValue, inputEvents }">
                                    <div class="d-flex align-items-center flex-fill bg-white">
                                        <input class="form-control border-0 bg-transparent shadow-none khmer-font py-2 ps-2" :value="range.start && range.end ? `${inputValue.start} — ${inputValue.end}` : ''" v-on="inputEvents.start" readonly placeholder="ជ្រើសរើសចន្លោះថ្ងៃ..." />
                                    </div>
                                </template>
                            </DatePicker>

                            <button @click="fetchAnalytics" class="btn btn-primary border-0 px-3 d-flex align-items-center justify-content-center" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                                <i v-else class="bi bi-arrow-clockwise fs-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3" v-for="(val, key) in stats" :key="key">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden card-hover">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div :class="`flex-shrink-0 p-3 rounded-3 bg-opacity-10 bg-${val.color} text-${val.color}`">
                                    <i :class="`bi ${val.icon} fs-4`"></i>
                                </div>
                                <div class="ms-3 text-truncate">
                                    <h6 class="text-muted small mb-1 khmer-font">{{ val.label }}</h6>
                                    <h4 class="fw-bold mb-0" v-if="!loading">{{ val.count }}</h4>
                                    <div v-else class="spinner-border spinner-border-sm text-secondary"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 khmer-font shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 fw-bold py-3 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bar-chart-line me-2"></i>សកម្មភាពកិច្ចប្រជុំប្រចាំខែ</span>
                            <span v-if="loading" class="badge bg-light text-primary fw-normal small">កំពុងទាញយក...</span>
                        </div>
                        <div class="card-body pt-0">
                            <div class="chart-container" style="position: relative; height: 350px;">
                                <canvas id="analyticsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4 d-print-none">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-12">
                            <div class="card border-0 khmer-font shadow-sm mb-xl-3 h-100">
                                <div class="card-header bg-transparent border-0 fw-bold py-3">
                                    <i class="bi bi-download me-2"></i>ទាញយករបាយការណ៍
                                </div>
                                <div class="card-body pt-0">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-light text-start border shadow-sm" @click="exportData('excel')">
                                            <i class="bi bi-file-earmark-excel text-success me-2"></i> Excel
                                        </button>
                                        <button class="btn btn-light text-start border shadow-sm" @click="windowPrint">
                                            <i class="bi bi-printer text-dark me-2"></i> បោះពុម្ព
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-12">
                            <div class="card border-0 khmer-font shadow-sm bg-primary text-white h-100 overflow-hidden">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="bi bi-cpu fs-6 text-white"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold small text-white">ស្ថានភាពប្រព័ន្ធ</h6>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1 small text-white opacity-90">
                                            <span><i class="bi bi-hdd-network me-1"></i> ទំហំផ្ទុក</span>
                                            <span class="fw-bold">05%</span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 6px; background: rgba(255,255,255,0.2)">
                                            <div class="progress-bar bg-white shadow-sm" role="progressbar"></div>
                                        </div>
                                    </div>
                                    <div class="p-2 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-10">
                                        <div class="d-flex align-items-center justify-content-between small text-white">
                                            <div class="d-flex align-items-center opacity-90 khmer-font">
                                                <i class="bi bi-send-fill me-2"></i> Telegram Bot
                                            </div>

                                            <div v-if="!loading">
                                                <span v-if="settings.enabled == 1 || settings.enabled === true" class="badge rounded-pill bg-white text-primary fw-bold" style="font-size: 0.7rem;">
                                                    <i class="bi bi-circle-fill me-1 text-success" style="font-size: 0.5rem;"></i> Active
                                                </span>
                                                
                                                <span v-else class="badge rounded-pill bg-white text-danger fw-bold" style="font-size: 0.7rem;">
                                                    <i class="bi bi-circle-fill me-1 text-danger" style="font-size: 0.5rem;"></i> Inactive
                                                </span>
                                            </div>
                                            
                                            <div v-else class="spinner-border spinner-border-sm text-white" role="status" style="width: 10px; height: 10px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import { ref, onMounted, shallowRef, nextTick } from 'vue';
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue';
    import HeaderBar from '@/components/HeaderBar.vue';
    import { AnalyticsService } from '@/services/AnalyticsService';
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/dist/style.css';
    import { Chart, LineController, LineElement, PointElement, LinearScale, Title, CategoryScale, Tooltip, Filler } from 'chart.js';

    Chart.register(LineController, LineElement, PointElement, LinearScale, Title, CategoryScale, Tooltip, Filler);

    const loading = ref(false);
    const chartInstance = shallowRef(null);

    const range = ref({
        start: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        end: new Date()
    });

    const stats = ref({
        total_meetings: { label: 'កិច្ចប្រជុំសរុប', count: 0, icon: 'bi-calendar-check', color: 'primary' },
        active_users: { label: 'អ្នកប្រើប្រាស់', count: 0, icon: 'bi-people', color: 'info' },
        attachments: { label: 'ឯកសារភ្ជាប់', count: 0, icon: 'bi-file-earmark-text', color: 'warning' },
        upcoming: { label: 'កិច្ចប្រជុំបន្តបន្ទាប់', count: 0, icon: 'bi-clock-history', color: 'success' },
    });

    const formatDate = (date) => {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString('en-CA');
    };

    const updateChart = (labels, values) => {
        nextTick(() => {
            const canvas = document.getElementById('analyticsChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            if (chartInstance.value) chartInstance.value.destroy();

            chartInstance.value = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels?.length ? labels : ['N/A'],
                    datasets: [{
                        label: 'កិច្ចប្រជុំ',
                        data: values?.length ? values : [0],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    };

    const fetchAnalytics = async () => {
        loading.value = true;
        const apiFilters = {
            start_date: formatDate(range.value.start),
            end_date: formatDate(range.value.end)
        };

        try {
            const [resSummary, resChart] = await Promise.all([
                AnalyticsService.getSummary(apiFilters),
                AnalyticsService.getChartData(apiFilters)
            ]);

            if (resSummary.data.status === 'success') {
                const d = resSummary.data.data;
                stats.value.total_meetings.count = d.total_meetings;
                stats.value.active_users.count = d.total_users;
                stats.value.attachments.count = d.total_attachments;
                stats.value.upcoming.count = d.upcoming_meetings;
            }

            if (resChart.data.status === 'success') {
                updateChart(resChart.data.labels, resChart.data.values);
            }
        } catch (error) {
            console.error("Analytics Error:", error);
        } finally {
            loading.value = false;
        }
    };

    const exportData = (type) => {
        const apiFilters = {
            start_date: formatDate(range.value.start),
            end_date: formatDate(range.value.end)
        };
        const url = AnalyticsService.getExportUrl(type, apiFilters);
        window.open(url, '_blank');
    };

    const windowPrint = () => window.print();

    const settings = ref({
        enabled: false,
        reminder_time: 15
    });

    const fetchSettings = async () => {
        try {
            loading.value = true;
            const res = await AnalyticsService.getNotificationSettings();
            
            settings.value = {
                enabled: Number(res.data.enabled) === 1, 
                reminder_time: parseInt(res.data.reminder_time) || 15
            };
        } catch (error) {
            console.error("Fetch Settings Error:", error);
        } finally {
            loading.value = false;
        }
    };

    onMounted(async () => {
        await Promise.all([
            fetchSettings(),
            fetchAnalytics()
        ]);
    });
</script>

<style scoped>
    @import url('@/assets/css/style.css');

    .custom-filter-group:focus-within {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1) !important;
    }
    .card-hover {
        transition: transform 0.2s;
    }
    .card-hover:hover {
        transform: translateY(-5px);
    }
</style>

