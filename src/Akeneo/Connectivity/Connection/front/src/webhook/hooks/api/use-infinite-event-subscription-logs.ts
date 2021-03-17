import {EventSubscriptionLog} from '../../model/EventSubscriptionLog';
import {RefObject, useCallback, useEffect, useRef, useState} from 'react';
import {useRoute} from '../../../shared/router';
import {useInfiniteScroll} from '../../scroll';
import {EventSubscriptionLogLevel} from '../../model/EventSubscriptionLogLevel';
// TODO import
import {useDebounceCallback} from '@akeneo-pim-community/shared';

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

export type Filters = {
    levels: EventSubscriptionLogLevel[],
};

/**
 * Scroll through the logs.
 */
const useInfiniteEventSubscriptionLogs = (
    connectionCode: string,
    filters: Filters,
    container: RefObject<HTMLElement>
): EventSubscriptionLogs => {
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
        filters: JSON.stringify(filters),
    };

    if (null !== searchAfter) {
        parameters['search_after'] = searchAfter;
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

    const {reset} = useInfiniteScroll<SearchEventSubscriptionLogsResponse>(fetchNextResponse, container);

    const isResetInitialized = useRef(false);
    const handleReset = useCallback(() => {
        setState({
            logs: [],
            total: undefined,
            page: 0,
            maxScrollReached: false,
            endScrollReached: false,
        });
        setSearchAfter(null);
        reset();
    }, [reset, setState, setSearchAfter]);
    const debounceReset = useDebounceCallback(handleReset, 1000);

    // write a custom useEffect, something like useEffectAfterRender
    useEffect(() => {
        if (!isResetInitialized.current) {
            isResetInitialized.current = true;
            return;
        }

        debounceReset();
    }, [filters, debounceReset, isResetInitialized]);

    return state;
};

export default useInfiniteEventSubscriptionLogs;
