import { Button, GraphIllustration, Information, RefreshIcon } from 'akeneo-design-system';
import React, { FC, useState, useRef } from 'react';
import { NoEventLogs } from './NoEventLogs';
import {useInfiniteScroll} from '../../common/hooks/useInfiniteScroll';
import {EventSubscriptionLog} from '../model/EventSubscriptionLog';
import {useRoute} from '../../shared/router';

type SearchEventSubscriptionLogsResponse = {
    results: EventSubscriptionLog[];
    total?: number;
    searchAfter?: string;
};

type EventSubscriptionLogs = {
    logs: EventSubscriptionLog[];
    searchAfter?: string;
    page: number,
    //initialized: boolean;
    total?: number;
    //fetchNextLogs: () => void;
    maxScrollReached: boolean;
    endScrollReached: boolean;
};

export const EventLogList: FC<{connectionCode: string}> = ({ connectionCode }) => {
    const [eventSubscriptionLogs, setEventSubscriptionLogs] = useState<EventSubscriptionLogs>({
        logs: [],
        page: 0,
        maxScrollReached: false,
        endScrollReached: false,
    });
    const url = useRoute(
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs',
        {
            connection_code: connectionCode,
            search_after: eventSubscriptionLogs.searchAfter || null
        }
    );
    const fetchNextResponse = async () => {
        const response = await fetch(url);
        const nextLogs = await response.json();

        setEventSubscriptionLogs(state => ({
            ...state,
            logs: [...state.logs, ...nextLogs.results],
            total: nextLogs.total,
            page: state.page + 1,
            searchAfter: nextLogs.searchAfter,
            endScrollReached: nextLogs.results.length === 0,
        }));

        return nextLogs;
    };
    const scrollContainer = useRef(null);

    useInfiniteScroll<SearchEventSubscriptionLogsResponse>(fetchNextResponse, scrollContainer);

    const {logs, page, total} = eventSubscriptionLogs;

    if (page === 0) {
        return null;
    }

    if (total === 0) {
        return <NoEventLogs/>;
    }

    console.log(eventSubscriptionLogs);

    return (
        <>
            <Information illustration={<GraphIllustration/>} title={`There is ${total} logs.`}>
                {null}
            </Information>
            <ul ref={scrollContainer}>
                {logs.map((log, index) => (
                    <li key={index} style={{height: '50px'}}>{log.timestamp} - {log.level} - {log.message}</li>
                ))}
            </ul>
        </>
    );
};
