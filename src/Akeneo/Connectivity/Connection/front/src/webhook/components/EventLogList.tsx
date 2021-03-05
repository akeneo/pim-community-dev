import {IconButton, RefreshIcon} from 'akeneo-design-system';
import React, {FC, useCallback, useEffect, useRef, useState} from 'react';
import {NoEventLogs} from './NoEventLogs';

function* fetchEventSubscriptionLogs(connectionCode: string, _searchAfter?: string) {
    const total = 73;

    let idx = total;
    while (idx > 0) {
        yield {
            results: new Array(idx > 25 ? 25 : idx).fill(0).map(
                (): Log => {
                    idx--;
                    return {
                        id: idx,
                        timestamp: idx * 3600,
                        level: 'warning',
                        message: 'Message!',
                        connection_code: connectionCode,
                        context: {},
                    };
                }
            ),
            search_after: '_encrypted_',
            total,
        };
    }
}

type Log = {
    id: number;
    timestamp: number;
    level: 'warning';
    message: string;
    connection_code: string;
    context: object;
};

type Data = {
    logs: Log[];
    total?: number;
    searchAfter?: string;
};

const useFetchEventSubscriptionLogs = (connectionCode: string) => {
    const [data, dispatch] = useState<Data>({logs: []});

    const generator = useRef(fetchEventSubscriptionLogs(connectionCode, data.searchAfter));
    const fetchNextLogs = useCallback(() => {
        const result = generator.current.next().value;
        if (!result) {
            return;
        }

        dispatch(({logs}) => ({
            logs: [...logs, ...result.results],
            total: result.total,
            searchAfter: result.search_after,
        }));
    }, [connectionCode]);

    return {logs: data.logs, total: data.total, fetchNextLogs};
};

export const EventLogList: FC<{connectionCode: string}> = ({connectionCode}) => {
    const {logs, total, fetchNextLogs} = useFetchEventSubscriptionLogs(connectionCode);

    useEffect(() => {
        fetchNextLogs();
    }, []);

    if (logs.length > 0) {
        return (
            <>
                Total: {total}
                <ul>
                    {logs.map(log => (
                        <li key={log.id}>
                            <code>{JSON.stringify(log)}</code>
                        </li>
                    ))}
                </ul>
                <IconButton
                    icon={<RefreshIcon />}
                    title='Load more!'
                    level='tertiary'
                    ghost='borderless'
                    onClick={() => fetchNextLogs()}
                />
            </>
        );
    }

    return <NoEventLogs />;
};
