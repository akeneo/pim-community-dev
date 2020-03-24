export type WeeklyEventCounts = {
    daily: {
        [eventDate: string]: number;
    };
    weekly_count: number;
};
