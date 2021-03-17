import React, {FC, useRef, useState} from 'react';
import styled from 'styled-components';
import {ArrowRightIcon, GraphIllustration, Information, Table} from 'akeneo-design-system';
import {NoEventLogs} from './NoEventLogs';
import {useTranslate} from '../../shared/translate';
import {EventLogBadge} from './EventLogBadge';
import EventLogDatetime from './EventLogDatetime';
import useInfiniteEventSubscriptionLogs, {Filters} from '../hooks/api/use-infinite-event-subscription-logs';
import {EventSubscriptionLogLevel} from '../model/EventSubscriptionLogLevel';
import {EventLogListFilters} from './EventLogListFilters';

const ExtraSmallColumnHeaderCell = styled(Table.HeaderCell)`
    width: 125px;
`;
const SmallColumnHeaderCell = styled(Table.HeaderCell)`
    width: 220px;
`;
const MessageContainer = styled.span`
    padding-right: 15px;
`;
const ContextContainer = styled.span`
    color: ${({theme}) => theme.color.grey100};
    white-space: nowrap;
    text-overflow: ellipsis;
    display: block;
    overflow: hidden;
`;

export const EventLogList: FC<{connectionCode: string}> = ({connectionCode}) => {
    const translate = useTranslate();
    const scrollContainer = useRef(null);
    const [filters, setFilters] = useState<Filters>({
        levels: [
            EventSubscriptionLogLevel.INFO,
            EventSubscriptionLogLevel.NOTICE,
            EventSubscriptionLogLevel.WARNING,
            EventSubscriptionLogLevel.ERROR,
        ],
    });
    // This is kinda crappy
    const [isSearchActive, setSearchActive] = useState(false);
    const {logs, page, total} = useInfiniteEventSubscriptionLogs(connectionCode, filters, scrollContainer);

    const handleFiltersChange = (filters: Filters) => {
        setFilters(filters);
        setSearchActive(true);
    };

    if (!isSearchActive && page === 0) {
        return null;
    }

    if (!isSearchActive && total === 0) {
        return <NoEventLogs />;
    }

    return (
        <>
            <Information
                illustration={<GraphIllustration />}
                title={translate('akeneo_connectivity.connection.webhook.event_logs.list.info.logs_total', {total: total ? total.toString() : '0'}, total)}
            >
                {null}
            </Information>
            <EventLogListFilters filters={filters} onChange={handleFiltersChange} total={total}/>
            <Table>
                <Table.Header>
                    <SmallColumnHeaderCell>
                        {translate('akeneo_connectivity.connection.webhook.event_logs.list.headers.datetime')}
                    </SmallColumnHeaderCell>
                    <ExtraSmallColumnHeaderCell>
                        {translate('akeneo_connectivity.connection.webhook.event_logs.list.headers.level')}
                    </ExtraSmallColumnHeaderCell>
                    <Table.HeaderCell>
                        {translate('akeneo_connectivity.connection.webhook.event_logs.list.headers.message')}
                    </Table.HeaderCell>
                </Table.Header>
                <Table.Body ref={scrollContainer}>
                    {logs.map(({timestamp, level, message, context}, index) => (
                        <Table.Row key={index} onClick={() => undefined}>
                            <Table.Cell>
                                <ArrowRightIcon />
                                <EventLogDatetime timestamp={timestamp * 1000} />
                            </Table.Cell>
                            <Table.Cell>
                                <EventLogBadge level={level}>{level.toUpperCase()}</EventLogBadge>
                            </Table.Cell>
                            <Table.Cell>
                                <MessageContainer>{message}</MessageContainer>
                                <ContextContainer>{JSON.stringify(context)}</ContextContainer>
                            </Table.Cell>
                        </Table.Row>
                    ))}
                </Table.Body>
            </Table>
        </>
    );
};
