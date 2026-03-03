import api from '@/api/axios';

const CONFIG = {
    DEFAULTS: {
        TIME: '00:00',
        END_TIME: '--:--',
        NO_DATA: 'មិនមាន',
        UNKNOWN: 'មិនមានបញ្ជាក់',
        LOCATION: 'សាលប្រជុំ'
    }
};

export const MeetingMonitor = {
    async getMeetingsByDate(date) {
        try {
            const response = await api.get('/schedules/public', { params: { date } });
            const rawData = response?.data?.data || response?.data;

            if (!Array.isArray(rawData)) return [];

            return this._normalizeData(rawData);
        } catch (error) {
            this._handleError(error);
            return [];
        }
    },

    _normalizeData(rawData) {
        return rawData.map((item) => {
            const start = item.startTime || item.start_time || CONFIG.DEFAULTS.TIME;
            const end = item.endTime || item.end_time || CONFIG.DEFAULTS.END_TIME;

            const participantsArray = Array.isArray(item.participants) ? item.participants : [];
            const participantsString = participantsArray.length > 0 
                ? participantsArray.join(', ') 
                : null;

            return {
                id: item.id,
                title: item.title || 'គ្មានចំណងជើង',
                startTime: start,
                endTime: end,
                
                attachment: item.attachment || item.file_path || null,
                floor: item.floor || null,
                room: item.room || null,
                
                leader: item.leader || item.participantsDisplay || participantsString || CONFIG.DEFAULTS.UNKNOWN,
                
                creator_name: item.creator_name || (item.user ? item.user.name : 'រដ្ឋបាល'),
                
                location: item.location || CONFIG.DEFAULTS.LOCATION,
                description: item.description || '',
                
                color_id: item.color_id || item.priority_id,
                rawStartTime: start
            };
        }).sort((a, b) => a.rawStartTime.localeCompare(b.rawStartTime));
    },

    _handleError(error) {
        console.error("❌ MeetingMonitor Error:", error.response?.data || error.message);
    }
};