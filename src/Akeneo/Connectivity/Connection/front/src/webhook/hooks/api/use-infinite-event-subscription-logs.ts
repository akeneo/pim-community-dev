import {EventSubscriptionLog} from '../../model/EventSubscriptionLog';
import {RefObject, useState} from 'react';
import {useRoute} from '../../../shared/router';
import {useInfiniteScroll} from '../../scroll';

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
    };

    if (null !== searchAfter) {
        parameters.search_after = searchAfter;
    }

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

    useInfiniteScroll<SearchEventSubscriptionLogsResponse>(fetchNextResponse, container);

    return state;
};

export default useInfiniteEventSubscriptionLogs;
