import { GraphIllustration, Information } from 'akeneo-design-system';
import React, { FC, useRef } from 'react';
import { NoEventLogs } from './NoEventLogs';
import useInfiniteEventSubscriptionLogs from '../hooks/api/useInfiniteEventSubscriptionLogs';

export const EventLogList: FC<{connectionCode: string}> = ({ connectionCode }) => {
    const scrollContainer = useRef(null);
    const { logs, page, total } = useInfiniteEventSubscriptionLogs(connectionCode, scrollContainer);

    if (page === 0) {
        return null;
    }

    if (total === 0) {
        return <NoEventLogs/>;
    }

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
