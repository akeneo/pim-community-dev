import {ArrowRightIcon, Badge, Button, GraphIllustration, Information, RefreshIcon, Table} from 'akeneo-design-system';
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
        const result = generator.current.next().value; //= await fetchEventSubscriptionLogs(connectionCode, data.searchAfter)
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
                <Information illustration={<GraphIllustration />} title={`There is ${total} logs.`}>
                    {null}
                </Information>
                <Table>
                    <Table.Header>
                        <Table.HeaderCell></Table.HeaderCell>
                        <Table.HeaderCell>Datetime</Table.HeaderCell>
                        <Table.HeaderCell>Level</Table.HeaderCell>
                        <Table.HeaderCell>Message</Table.HeaderCell>
                    </Table.Header>
                    <Table.Body>
                        {logs.map(({id, timestamp, level, message}) => (
                            <Table.Row key={id} onClick={() => undefined}>
                                <Table.Cell>
                                    <ArrowRightIcon />
                                </Table.Cell>
                                <Table.Cell>
                                    {new Intl.DateTimeFormat('en-US', {
                                        year: 'numeric',
                                        month: '2-digit',
                                        day: '2-digit',
                                    }).format(new Date(timestamp))}
                                    <br />
                                    {new Intl.DateTimeFormat('en-US', {
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit',
                                    }).format(new Date(timestamp))}
                                </Table.Cell>
                                <Table.Cell>
                                    <Badge level='warning'>{level.toUpperCase()}</Badge>
                                </Table.Cell>
                                <Table.Cell>{message}</Table.Cell>
                            </Table.Row>
                        ))}
                    </Table.Body>
                </Table>
                <br />
                <Button ghost level='tertiary' onClick={() => fetchNextLogs()}>
                    <RefreshIcon /> Load more…
                </Button>
            </>
        );
    }

    return <NoEventLogs />;
};
