import {EventSubscriptionLogLevel} from './EventSubscriptionLogLevel';
import {isEqual} from 'lodash';

export type EventSubscriptionLogFilters = {
    levels: EventSubscriptionLogLevel[],
    text: string,
};

export const DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS = {
    levels: [
        EventSubscriptionLogLevel.INFO,
        EventSubscriptionLogLevel.NOTICE,
        EventSubscriptionLogLevel.WARNING,
        EventSubscriptionLogLevel.ERROR,
    ],
    text: '',
};

export const isSameAsDefaultFiltersValues = (filters: EventSubscriptionLogFilters): boolean => {
    return isEqual(filters, DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS);
};
