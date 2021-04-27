import {isEqual} from 'lodash';
import {EventSubscriptionLogLevel} from './EventSubscriptionLogLevel';

export type EventSubscriptionLogFilters = {
    levels: EventSubscriptionLogLevel[];
    text: string;
    dateTimeStart?: number;
    dateTimeEnd?: number;
};

export const getDefaultFilters: () => EventSubscriptionLogFilters = () => ({
    levels: [
        EventSubscriptionLogLevel.INFO,
        EventSubscriptionLogLevel.NOTICE,
        EventSubscriptionLogLevel.WARNING,
        EventSubscriptionLogLevel.ERROR,
    ],
    text: '',
    dateTimeStart: undefined,
    dateTimeEnd: undefined,
});

export const isSameAsDefaultFiltersValues = (filters: EventSubscriptionLogFilters): boolean => {
    return isEqual(filters, getDefaultFilters());
};
