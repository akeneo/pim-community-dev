export type WeeklyEventCounts = {
    daily: {
        [eventDate: string]: number;
    };
    weekly_total: number;
};
