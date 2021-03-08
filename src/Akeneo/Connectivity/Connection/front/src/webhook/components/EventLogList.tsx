import {ArrowRightIcon, Badge, Button, GraphIllustration, Information, RefreshIcon, Table} from 'akeneo-design-system';
import React, {FC, useCallback, useEffect, useRef, useState} from 'react';
import {NoEventLogs} from './NoEventLogs';


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
