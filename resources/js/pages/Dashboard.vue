<template>
    <DashboardLayout>
        <template #header>
            <HeaderBar />
        </template>
        <div class="row g-3 h-100">
            <div class="col-lg-3 d-flex flex-column h-100">
                <LeftPanel :featuredTime="'7:30'" leaderName="ឯកឧត្តម យើត វីណែល" />
            </div>

            <div class="col-lg-9 d-flex flex-column h-100">
                <MeetingList :redLineTop="62">
                    <template #morning>
                        <MeetingCard v-for="meeting in morningList" :key="meeting.id" :variant="meeting.color" :time="meeting.time" :title="meeting.title" :desc="meeting.desc" />
                    </template>

                    <template #afternoon>
                        <MeetingCard v-for="meeting in afternoonList" :key="meeting.id" :variant="meeting.color" :time="meeting.time" :title="meeting.title" :desc="meeting.desc" />
                    </template>
                </MeetingList>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
    import DashboardLayout from '@/components/layouts/DashboardLayout.vue'
    import HeaderBar from '@/components/HeaderBar.vue'
    import LeftPanel from '@/components/LeftPanel.vue'
    import MeetingList from '@/components/MeetingList.vue'
</script>


