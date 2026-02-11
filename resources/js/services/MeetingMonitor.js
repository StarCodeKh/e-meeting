import api from '@/api/axios';

/**
 * Configuration Constants
 * ដាក់នៅខាងក្រៅ Class/Object ដើម្បីកាត់បន្ថយការប្រើប្រាស់ Memory និងងាយស្រួលកែសម្រួល
 */
const CONFIG = {
    COLORS: {
        red: 'bg-coral',
        yellow: 'bg-orange',
        green: 'bg-success',
    },
    TAGS: {
        red: 'tag-red',
        yellow: 'tag-yellow',
        green: 'tag-green',
    },
    DEFAULTS: {
        TIME: '០០:០០',
        NO_DATA: 'មិនមាន',
        UNKNOWN: 'មិនមានបញ្ជាក់'
    }
};

export const MeetingMonitor = {
    /**
     * ទាញយក និងរៀបចំទិន្នន័យកិច្ចប្រជុំ (Main Method)
     * @param {string} date - ទម្រង់កាលបរិច្ឆេទ YYYY-MM-DD
     */
    async getMeetingsByDate(date) {
        try {
            const response = await api.get('/schedules', { params: { date } });
            const rawData = response?.data?.data;

            if (!Array.isArray(rawData)) {
                return [];
            }

            return this._normalizeData(rawData);
        } catch (error) {
            this._handleError(error);
            return [];
        }
    },

    /**
     * បំប្លែងទិន្នន័យពី API ឱ្យទៅជាទម្រង់ Standard សម្រាប់ UI (Private-like method)
     */
    _normalizeData(rawData) {
        const normalized = rawData.map((item) => {
            const participants = Array.isArray(item.participants) ? item.participants : [];
            
            return {
                id: item.id,
                title: item.title ?? 'គ្មានចំណងជើង',
                startTime: item.start_time ?? '00:00',
                endTime: item.end_time ?? '--:--',
                
                // Data Logic
                participantsRaw: participants,
                participantsDisplay: participants.length > 0 ? participants.join(', ') : CONFIG.DEFAULTS.NO_DATA,
                host: participants[0] ?? CONFIG.DEFAULTS.UNKNOWN,
                
                // Info Logic
                location: item.location ?? CONFIG.DEFAULTS.UNKNOWN,
                room: item.room ?? 'N/A',
                description: item.description ?? 'មិនមានការពិពណ៌នា',
                
                // Style Mapping Logic
                colorClass: CONFIG.COLORS[item.color_id] ?? CONFIG.COLORS.red,
                tagClass: CONFIG.TAGS[item.color_id] ?? CONFIG.TAGS.red,
                
                // Sort Key
                rawStartTime: item.start_time ?? '00:00'
            };
        });

        return this._sortMeetings(normalized);
    },

    /**
     * តម្រៀបបញ្ជីកិច្ចប្រជុំតាមម៉ោង (Helper method)
     */
    _sortMeetings(meetings) {
        return meetings.sort((a, b) => a.rawStartTime.localeCompare(b.rawStartTime));
    },

    /**
     * គ្រប់គ្រង Error តាមបទដ្ឋាន Standard (Error Handler)
     */
    _handleError(error) {
        const message = error.response 
            ? `Server Error [${error.response.status}]` 
            : error.request 
            ? 'Network Error (No Response)' 
            : `Request Error: ${error.message}`;
        console.error(`❌ MeetingMonitor: ${message}`);
    }
};