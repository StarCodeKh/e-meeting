import api from '@/api/axios';

export const MeetingService = {
    async getMeetingsByDate(date) {
        try {
            const response = await api.get(`/schedules?date=${date}`);
            const rawData = response.data.data;

            // ចាប់ផ្ដើមការ Map ទិន្នន័យឱ្យដូច Format ចាស់របស់អ្នក
            return rawData.map(item => {
                const startDate = new Date(item.start_time);
                const hours = startDate.getHours();
                const minutes = String(startDate.getMinutes()).padStart(2, '0');

                // ១. កំណត់ម៉ោងសម្រាប់បង្ហាញ (ឧទាហរណ៍៖ 14:30 ទៅជា 2:30)
                const displayHour = hours % 12 || 12;
                
                // ២. កំណត់ Period (ព្រឹក/រសៀល)
                const period = hours >= 12 ? 'រសៀល' : 'ព្រឹក';

                // ៣. កំណត់ Session សម្រាប់ Filter (morning/afternoon)
                const session = hours >= 12 ? 'afternoon' : 'morning';

                // ៤. កំណត់ colorClass ផ្អែកលើ 'type' របស់ API
                const colorMap = {
                    'meeting': 'bg-coral',
                    'appointment': 'bg-orange',
                    'task': 'bg-success'
                };

                return {
                    id: item.id,
                    time: `${displayHour}:${minutes}`,
                    period: period,
                    session: session,
                    colorClass: colorMap[item.type] || 'bg-sky',
                    title: item.title,
                    description: item.description || 'មិនមានការពិពណ៌នា'
                };
            });
        } catch (error) {
            console.error("Fetch Error:", error);
            return [];
        }
    }
};