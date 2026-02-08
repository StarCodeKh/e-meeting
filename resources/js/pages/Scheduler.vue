<template>
<div class="scheduler-wrapper">
<div class="scheduler-container">


<!-- LEFT SIDEBAR -->
<Sidebar />


<!-- MAIN CONTENT -->
<div class="main-content">
<TopBar />
<CalendarHeader />
<Timeline />
</div>


</div>
</div>
</template>


<script setup>
import Sidebar from '../components/Sidebar.vue'
import TopBar from '../components/TopBar.vue'
import CalendarHeader from '../components/CalendarHeader.vue'
import Timeline from '../components/Timeline.vue'
</script>


<style scoped>
.scheduler-wrapper {
padding: 20px;
background: #f1f2f4;
}


.scheduler-container {
background: #e0e0e0;
border-radius: 24px;
display: flex;
overflow: hidden;
min-height: 90vh;
}


.main-content {
flex: 1;
background: #f8f9fa;
padding: 20px;
}


@media (max-width: 992px) {
.scheduler-container {
flex-direction: column;
}
}
</style>