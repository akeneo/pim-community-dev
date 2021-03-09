import { Button, GraphIllustration, Information, RefreshIcon } from 'akeneo-design-system';
import React, { FC, useEffect, useRef } from 'react';
import { NoEventLogs } from './NoEventLogs';
import useFetchEventSubscriptionLogs from '../hooks/api/use-fetch-event-subscription-logs';

export const EventLogList: FC<{connectionCode: string}> = ({ connectionCode }) => {
    const {
        logs,
        initialized,
        total,
        fetchNextLogs,
        maxScrollReached,
        endScrollReached
    } = useFetchEventSubscriptionLogs(connectionCode);

    useEffect(() => {
        fetchNextLogs();
    }, []);

    const scrollContainer = useRef(null);

    if (!initialized) {
        return null;
    }

    if (total === 0) {
        return <NoEventLogs/>;
    }

    console.log(logs);

    return (
        <>
            <Information illustration={<GraphIllustration/>} title={`There is ${total} logs.`}>
                {null}
            </Information>
            <ul ref={container}>
                {logs.map((log, index) => (
                    <li key={index} style={{height: "50px"}}>{log.timestamp} - {log.level} - {log.message}</li>
                ))}
            </ul>
        </>
    );
};
