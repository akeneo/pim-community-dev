import React, {FC, useContext, useRef, useState} from 'react';
import styled from 'styled-components';
import {ArrowRightIcon, ArrowDownIcon, GraphIllustration, Information, Table, Link} from 'akeneo-design-system';
import {NoEventLogs} from './NoEventLogs';
import {useTranslate} from '../../shared/translate';
import {EventLogBadge} from './EventLogBadge';
import EventLogDatetime from './EventLogDatetime';
import useInfiniteEventSubscriptionLogs from '../hooks/api/use-infinite-event-subscription-logs';
import {EventLogListFilters} from './EventLogListFilters';
import ExpandableTableRow, {IsExpanded} from '../../common/components/ExpandableTableRow';
import FormattedJSON from '../../common/components/FormattedJSON';
import {
    EventSubscriptionLogFilters,
    DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS,
    isSameAsDefaultFiltersValues,
} from '../model/EventSubscriptionLogFilters';
import {NoEventLogsWithThoseFilters} from './NoEventLogsWithThoseFilters';

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

const Arrow: FC = () => {
    const isExpanded = useContext(IsExpanded);

    return isExpanded ? <ArrowDownIcon /> : <ArrowRightIcon />;
};

export const EventLogList: FC<{connectionCode: string}> = ({connectionCode}) => {
    const translate = useTranslate();
    const scrollContainer = useRef(null);
    const [filters, setFilters] = useState<EventSubscriptionLogFilters>(DEFAULT_EVENT_SUBSCRIPTION_LOG_FILTERS);
    const isSearchActive = !isSameAsDefaultFiltersValues(filters);

    const {logs, total, isLoading} = useInfiniteEventSubscriptionLogs(connectionCode, filters, scrollContainer);

    if (!isSearchActive && !isLoading && total === 0) {
        return <NoEventLogs />;
    }

    return (
        <>
            <Information
                illustration={<GraphIllustration />}
                title={translate('akeneo_connectivity.connection.webhook.event_logs.list.info.title')}
            >
                <div>{translate('akeneo_connectivity.connection.webhook.event_logs.list.info.content')}</div>
                <Link
                    target={'_blank'}
                    href={'https://api.akeneo.com/events-documentation/subscription.html#debugging-events'}
                >
                    {translate('akeneo_connectivity.connection.webhook.event_logs.list.info.link')}
                </Link>
            </Information>
            <EventLogListFilters filters={filters} onChange={setFilters} total={total} />
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
                        <ExpandableTableRow key={index} contentToExpand={<FormattedJSON>{context}</FormattedJSON>}>
                            <Table.Cell>
                                <Arrow />
                                <EventLogDatetime timestamp={timestamp * 1000} />
                            </Table.Cell>
                            <Table.Cell>
                                <EventLogBadge level={level}>{level.toUpperCase()}</EventLogBadge>
                            </Table.Cell>
                            <Table.Cell>
                                <MessageContainer>{message}</MessageContainer>
                                <ContextContainer>{JSON.stringify(context)}</ContextContainer>
                            </Table.Cell>
                        </ExpandableTableRow>
                    ))}
                </Table.Body>
            </Table>
            {isSearchActive && total === 0 && <NoEventLogsWithThoseFilters />}
        </>
    );
};
