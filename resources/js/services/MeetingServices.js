import api from '@/api/axios';

export const MeetingServices = {
    /**
     * Fetch meeting data and transform into a standard clean object
     * @param {string} date - Format YYYY-MM-DD
     */
    async getMeetingsByDate(date) {
        try {
            const response = await api.get('/schedules', { params: { date } });
            const rawData = response?.data?.data;

            if (!Array.isArray(rawData)) return [];

            // Transform and sort by start time
            return rawData
                .map(item => this._transformMeeting(item))
                .sort((a, b) => a.startTime.localeCompare(b.startTime));
        } catch (error) {
            console.error("❌ MeetingServices Error:", error);
            return [];
        }
    },

    /**
     * Private Transformation Logic
     * Converts Raw API Data into a consistent Frontend Object
     */
    _transformMeeting(item) {
        // 1. Time & Session Logic
        const startTime = item.start_time ?? '00:00';
        const [h, m] = startTime.split(':');
        const hours = parseInt(h);
        
        const displayHour = hours % 12 || 12;
        const period = hours >= 12 ? 'រសៀល' : 'ព្រឹក';
        const session = hours >= 12 ? 'afternoon' : 'morning';

        // 2. Participants Logic
        const participants = Array.isArray(item.participants) ? item.participants : [];

        // 3. Standardized Object Return
        return {
            id: item.id,
            title: item.title ?? 'គ្មានចំណងជើង',
            description: item.description ?? 'មិនមានការពិពណ៌នា',
            
            // Critical: Pass the raw color_id for dynamic matching
            color_id: item.color_id, 

            // Time Data
            startTime: startTime,      // 24h format (sorting/logic)
            endTime: item.end_time ?? '--:--',
            time: `${displayHour}:${m}`, // 12h format (display)
            period: period,            // ព្រឹក or រសៀល
            session: session,          // morning or afternoon

            // People Data
            participantsRaw: participants,
            participantsDisplay: participants.length > 0 ? participants.join(', ') : 'មិនមាន',
            host: participants[0] ?? 'មិនមានបញ្ជាក់',

            // Assets & Links
            attachmentUrl: item.attachment || item.file_path || null,
            link: item.link ?? '#',

            // Location Data
            location: item.location ?? 'មិនមានបញ្ជាក់',
            room: item.room ?? 'N/A'
        };
    }
};