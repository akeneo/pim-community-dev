import {WeeklyEventCounts} from './weekly-event-counts';

export type ConnectionsAuditData = {
    [connectionCode: string]: WeeklyEventCounts;
};
