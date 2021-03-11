import {useCallback, useState} from 'react';
import {useRoute} from '../../../shared/router';
import {EventSubscriptionLog} from '../../model/EventSubscriptionLog';

type Data = {
    logs: EventSubscriptionLog[];
    total?: number;
    searchAfter?: string;
    page: number;
    endScrollReached: boolean;
};

type SearchEventSubscriptionLogsResponse = {
    results: EventSubscriptionLog[];
    total?: number;
    searchAfter?: string;
};

type EventSubscriptionLogs = {
    logs: EventSubscriptionLog[];
    initialized: boolean;
    total?: number;
    fetchNextLogs: () => void;
    maxScrollReached: boolean;
    endScrollReached: boolean;
};
/** TO REMOVE */
const useFetchEventSubscriptionLogs = (connectionCode: string): EventSubscriptionLogs => {
    const [data, setData] = useState<Data>({
        logs: [],
        page: 0,
        endScrollReached: false
    });
    const url = useRoute(
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs',
        {
            connection_code: connectionCode,
            search_after: data.searchAfter || null
        }
    );

    const fetchNextLogs = useCallback(async () => {
        if (data.endScrollReached) {
            return;
        }

        const response = await fetch(url);
        const nextLogs: SearchEventSubscriptionLogsResponse = await response.json();

        setData(({logs, page}) => ({
            logs: [...logs, ...nextLogs.results],
            initialized: true,
            total: nextLogs.total,
            page: page + 1,
            searchAfter: nextLogs.searchAfter,
            endScrollReached: nextLogs.results.length === 0,
        }));
    }, [url, data.endScrollReached]);

    return {
        logs: data.logs,
        initialized: data.total !== undefined,
        total: data.total,
        fetchNextLogs,
        maxScrollReached: data.page >= 20,
        endScrollReached: data.endScrollReached
    };
};

export default useFetchEventSubscriptionLogs;
