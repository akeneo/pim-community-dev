import {EventSubscriptionLogLevel} from './EventSubscriptionLogLevel';

export type EventSubscriptionLogFilters = {
    levels: EventSubscriptionLogLevel[],
};

export const DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS = {
    levels: [
        EventSubscriptionLogLevel.INFO,
        EventSubscriptionLogLevel.NOTICE,
        EventSubscriptionLogLevel.WARNING,
        EventSubscriptionLogLevel.ERROR,
    ],
};

// This is calculated only once, to avoid unecessary computing when comparing it.
const DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS_JSON = JSON.stringify(DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS);

export const isSameAsDefaultFiltersValues = (filters: EventSubscriptionLogFilters): boolean => {
    return JSON.stringify(filters) === DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS_JSON;
};
