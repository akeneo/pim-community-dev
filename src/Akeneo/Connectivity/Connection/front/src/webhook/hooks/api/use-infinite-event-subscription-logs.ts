import {EventSubscriptionLog} from '../../model/EventSubscriptionLog';
import {RefObject, useCallback, useState} from 'react';
import {useRoute} from '../../../shared/router';
import {useInfiniteScroll} from '../../scroll';
import {useEffectAfterFirstRender} from '../../../shared/hooks/useEffectAfterFirstRender';
import {useDebounceCallback} from '../../../shared/utils/use-debounce-callback';
import {EventSubscriptionLogFilters, isSameAsDefaultFiltersValues} from '../../model/EventSubscriptionLogFilters';

const MAX_PAGES = 20;

type SearchEventSubscriptionLogsResponse = {
    results: EventSubscriptionLog[];
    total?: number;
    search_after?: string;
};

type EventSubscriptionLogs = {
    logs: EventSubscriptionLog[];
    total?: number;
    page: number;
    maxScrollReached: boolean;
    endScrollReached: boolean;
};

/**
 * Scroll through the logs.
 */
const useInfiniteEventSubscriptionLogs = (
    connectionCode: string,
    filters: EventSubscriptionLogFilters,
    container: RefObject<HTMLElement>
): EventSubscriptionLogs & {
    isLoading: boolean;
} => {
    const [state, setState] = useState<EventSubscriptionLogs>({
        logs: [],
        total: undefined,
        page: 0,
        maxScrollReached: false,
        endScrollReached: false,
    });

    const [searchAfter, setSearchAfter] = useState<string | null>(null);
    const {maxScrollReached, endScrollReached} = state;

    const parameters: {
        [name: string]: string;
    } = {
        connection_code: connectionCode,
    };

    if (null !== searchAfter) {
        parameters.search_after = searchAfter;
    }

    if (!isSameAsDefaultFiltersValues(filters)) {
        parameters.filters = JSON.stringify({
            levels: filters.levels,
            text: filters.text,
            timestamp_from: filters.dateTime.start || null,
            timestamp_to: filters.dateTime.end || null,
        });
    }

    // This damn hook forbid us to build the url inside fetchNextResponse()
    const url = useRoute(
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs',
        parameters
    );

    const fetchNextResponse = async (): Promise<SearchEventSubscriptionLogsResponse | null> => {
        if (maxScrollReached || endScrollReached) {
            return null;
        }

        const response = await fetch(url);
        const payload = await response.json();

        setSearchAfter(payload.search_after);
        setState(state => ({
            ...state,
            logs: [...state.logs, ...payload.results],
            total: payload.total,
            page: state.page + 1,
            endScrollReached: payload.results.length === 0,
            maxScrollReached: state.page >= MAX_PAGES,
        }));

        return payload;
    };

    const {reset, isLoading} = useInfiniteScroll<SearchEventSubscriptionLogsResponse>(fetchNextResponse, container);

    const resetState = useCallback(() => {
        setState({
            logs: [],
            total: undefined,
            page: 0,
            maxScrollReached: false,
            endScrollReached: false,
        });
        setSearchAfter(null);
    }, [setState, setSearchAfter]);

    const resetInfiniteScroll = useDebounceCallback(reset, 300);

    // By default, an useEffect is always executed during the first render.
    // Here, we want to trigger this useEffect only after the initial render,
    // when the filters are updated.
    useEffectAfterFirstRender(() => {
        // First, reset the local state, empty logs.
        resetState();
        // Then, reset the infinite scroll to start fetching from the beginning
        resetInfiniteScroll();
    }, [filters, resetState, resetInfiniteScroll]);

    return {
        ...state,
        isLoading: isLoading,
    };
};

export default useInfiniteEventSubscriptionLogs;
