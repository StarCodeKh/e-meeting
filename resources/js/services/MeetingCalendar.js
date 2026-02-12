import api from '@/api/axios';

const UI_CONFIG = {
    COLORS: { red: 'bg-danger', yellow: 'bg-warning', green: 'bg-success' }
};

export const MeetingCalendar = {
    async getMeetingsByMonth(month, year) {
        const response = await api.get('/schedules/calendar', { params: { month, year } });
        return (response.data.data || []).map(item => this._transformMeeting(item));
    },

    async getMeetingsByDate(date) {
        const response = await api.get('/schedules/calendar', { params: { date } });
        return (response.data.data || []).map(item => this._transformMeeting(item));
    },

    _transformMeeting(item) {
        const formatTime = (t) => {
            if (!t) return { display: '--:--', hour: 0 };
            const [h, m] = t.split(':');
            const hrs = parseInt(h);
            return { display: `${hrs % 12 || 12}:${m}`, hour: hrs };
        };

        const start = formatTime(item.start_time);
        const end = formatTime(item.end_time);

        return {
            ...item,
            time: start.display,
            endTime: end.display,
            endTimeRaw: item.end_time,
            period: start.hour >= 12 ? 'រសៀល' : 'ព្រឹក',
            session: start.hour >= 12 ? 'afternoon' : 'morning',
            colorClass: UI_CONFIG.COLORS[item.color_id] || 'bg-primary'
        };
    }
};
