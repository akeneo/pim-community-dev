import {isEqual} from 'lodash';
import {DateTime} from 'luxon';
import {EventSubscriptionLogLevel} from './EventSubscriptionLogLevel';

export type EventSubscriptionLogFilters = {
    levels: EventSubscriptionLogLevel[];
    text: string;
    dateTime: {
        start?: number;
        end?: number;
    };
};

export const getDefaultFilters: () => EventSubscriptionLogFilters = () => ({
    levels: [
        EventSubscriptionLogLevel.INFO,
        EventSubscriptionLogLevel.NOTICE,
        EventSubscriptionLogLevel.WARNING,
        EventSubscriptionLogLevel.ERROR,
    ],
    text: '',
    dateTime: {},
});

export const isSameAsDefaultFiltersValues = (filters: EventSubscriptionLogFilters): boolean => {
    return isEqual(filters, getDefaultFilters());
};

export type FiltersConfig = {
    dateTime: {
        min: number;
        max: number;
    };
};

export const getFiltersConfig: () => FiltersConfig = () => ({
    dateTime: {
        min: 0,
        max: DateTime.utc().toSeconds(),
    },
});
