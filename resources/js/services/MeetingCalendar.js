import api from '@/api/axios';

/**
 * Configuration Mapping
 * រក្សាទុកនៅខាងក្រៅ Method ដើម្បី Performance និងងាយស្រួល Maintain
 */
const UI_CONFIG = {
    COLORS: {
        red: 'bg-coral',
        yellow: 'bg-orange',
        green: 'bg-success',
    },
    TAGS: {
        red: 'tag-red',
        yellow: 'tag-yellow',
        green: 'tag-green',
    }
};

export const MeetingCalendar = {
    /**
     * ទាញយកទិន្នន័យកិច្ចប្រជុំ និងរៀបចំទម្រង់ Standard
     * @param {string} date - ទម្រង់ YYYY-MM-DD
     */
    async getMeetingsByDate(date) {
        try {
            const response = await api.get('/schedules/calendar', { params: { date } });
            const rawData = response?.data?.data;

            if (!Array.isArray(rawData)) return [];

            return rawData.map(item => this._transformMeeting(item))
                          .sort((a, b) => a.startTime.localeCompare(b.startTime));
        } catch (error) {
            console.error("❌ MeetingCalendar Error:", error);
            return [];
        }
    },

    /**
     * Private Transformation Logic
     * បំប្លែង Raw Data ទៅជា Clean Object
     */
    _transformMeeting(item) {
        // ១. រៀបចំ Logic ម៉ោង (១២ម៉ោង និង ព្រឹក/រសៀល)
        const startTime = item.start_time ?? '00:00';
        const [h, m] = startTime.split(':');
        const hours = parseInt(h);
        
        const displayHour = hours % 12 || 12;
        const period = hours >= 12 ? 'រសៀល' : 'ព្រឹក';
        const session = hours >= 12 ? 'afternoon' : 'morning';

        // ២. រៀបចំបញ្ជីអ្នកចូលរួម (Participants)
        const participants = Array.isArray(item.participants) ? item.participants : [];

        return {
            id: item.id,
            title: item.title ?? 'គ្មានចំណងជើង',
            
            // Time Properties
            startTime: startTime, // ២៤ម៉ោង (សម្រាប់ logic)
            endTime: item.end_time ?? '--:--',
            time: `${displayHour}:${m}`, // ១២ម៉ោង (សម្រាប់ UI)
            period,
            session,

            // Participant Properties
            participantsRaw: participants,
            participantsDisplay: participants.length > 0 ? participants.join(', ') : 'មិនមាន',
            host: participants[0] ?? 'មិនមានបញ្ជាក់',

            // UI Styling Classes
            colorClass: UI_CONFIG.COLORS[item.color_id] ?? UI_CONFIG.COLORS.red,
            tagClass: UI_CONFIG.TAGS[item.color_id] ?? UI_CONFIG.TAGS.red,

            // Metadata
            location: item.location ?? 'មិនមានបញ្ជាក់',
            room: item.room ?? 'N/A',
            description: item.description ?? 'មិនមានការពិពណ៌នា',
            link: item.link ?? '#',
            attachment: item.attachment ?? null,
        };
    }
};