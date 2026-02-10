import api from '@/api/axios';

export const MeetingServices = {
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
                    'red': 'bg-coral',
                    'yellow': 'bg-orange',
                    'green': 'bg-success',
                };

                const tagMap = {
                    'red': 'tag-red',
                    'yellow': 'tag-yellow',
                    'green': 'tag-green',
                };

                return {
                    id: item.id,
                    title: item.title,
                    time: `${displayHour}:${m}`, 
                    period: period,
                    session: session,
                    colorClass: colorMap[item.color_id] || 'bg-coral',
                    tagClass: tagMap[item.color_id] || 'tag-red',
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