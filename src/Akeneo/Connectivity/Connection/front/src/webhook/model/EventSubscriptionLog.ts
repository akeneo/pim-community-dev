import {EventSubscriptionLogLevel} from './EventSubscriptionLogLevel';

export type EventSubscriptionLog = {
    timestamp: number;
    level: EventSubscriptionLogLevel;
    message: string;
    connection_code: string|null;
    context: object;
};
