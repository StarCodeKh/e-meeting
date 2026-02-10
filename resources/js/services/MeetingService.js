import api from '@/api/axios';

export const MeetingService = {
    async getMeetingsByDate(date) {
        try {
            const response = await api.get(`/schedules`, { params: { date } });
            const rawData = response.data.data;

            if (!rawData) return [];

            return rawData.map(item => {
                const [h, m] = item.start_time.split(':');
                const hours = parseInt(h);
                
                const displayHour = hours % 12 || 12;
                const period = hours >= 12 ? 'រសៀល' : 'ព្រឹក';
                const session = hours >= 12 ? 'afternoon' : 'morning';

                const colorMap = {
                    'meeting': 'bg-coral',
                    'appointment': 'bg-orange',
                    'task': 'bg-success'
                };

                return {
                    id: item.id,
                    title: item.title,
                    time: `${displayHour}:${m}`, 
                    period: period,
                    session: session,
                    colorClass: colorMap[item.type] || 'bg-sky',
                    description: item.description,
                    location: item.location,
                    room: item.room,
                    participants: item.participants || []
                };
            }).sort((a, b) => a.time.localeCompare(b.time));
        } catch (error) {
            console.error("Fetch Error:", error);
            return [];
        }
    }
};