import {
    ArrowDownIcon,
    ArrowRightIcon,
    GraphIllustration,
    Information,
    Link,
    Table,
    getColor,
} from 'akeneo-design-system';
import React, {FC, useContext, useRef, useState} from 'react';
import styled, {keyframes} from 'styled-components';
import ExpandableTableRow, {IsExpanded} from '../../common/components/ExpandableTableRow';
import FormattedJSON from '../../common/components/FormattedJSON';
import {useTranslate} from '../../shared/translate';
import useInfiniteEventSubscriptionLogs from '../hooks/api/use-infinite-event-subscription-logs';
import {
    EventSubscriptionLogFilters,
    getDefaultFilters,
    isSameAsDefaultFiltersValues,
} from '../model/EventSubscriptionLogFilters';
import {EventLogBadge} from './EventLogBadge';
import EventLogDatetime from './EventLogDatetime';
import {EventLogListFilters} from './EventLogListFilters';
import {NoEventLogs} from './NoEventLogs';
import {NoEventLogsWithThoseFilters} from './NoEventLogsWithThoseFilters';

const loadingBreath = keyframes`
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
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
const LoadingRow = styled(Table.Row)`
    border-width: 5px 0px;
    border-color: ${getColor('white')};
    border-style: solid;
`;
const LoadingCell = styled(Table.Cell)`
    height: 65px;
    animation: ${loadingBreath} 2s infinite;
    content: '';
    top: 0px;
    left: 0px;
    width: 100%;
    background: linear-gradient(270deg, #fdfdfd, #eee);
    background-size: 400% 400%;
    border-radius: 5px;
`;

const Arrow: FC = () => {
    const isExpanded = useContext(IsExpanded);

    return isExpanded ? <ArrowDownIcon /> : <ArrowRightIcon />;
};

const SESSION_STORAGE_KEY = 'connectivity_connection_event_subscription_logs_filters_2';

const loadFiltersFromTheSession = () => {
    const sessionFilters = sessionStorage.getItem(SESSION_STORAGE_KEY);

    return null !== sessionFilters ? JSON.parse(sessionFilters) : null;
};

const saveFiltersIntoTheSession = (filters: EventSubscriptionLogFilters) => {
    sessionStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(filters));
};

export const EventLogList: FC<{connectionCode: string}> = ({connectionCode}) => {
    const translate = useTranslate();
    const scrollContainer = useRef(null);

    const [filters, setFilters] = useState<EventSubscriptionLogFilters>(
        loadFiltersFromTheSession() || getDefaultFilters()
    );
    const isSearchActive = !isSameAsDefaultFiltersValues(filters);

    const {logs, total, isLoading, isInitialized} = useInfiniteEventSubscriptionLogs(
        connectionCode,
        filters,
        scrollContainer
    );

    if (!isInitialized) {
        return null;
    }

    if (!isSearchActive && !isLoading && total === 0) {
        return <NoEventLogs />;
    }

    const handleFiltersChange = (filters: EventSubscriptionLogFilters) => {
        setFilters(state => ({...state, ...filters}));
        saveFiltersIntoTheSession(filters);
    };

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
            <EventLogListFilters filters={filters} onChange={handleFiltersChange} total={total} />
            <Table style={{tableLayout: 'fixed'}}>
                <colgroup>
                    <col style={{width: 220}}></col>
                    <col style={{width: 125}}></col>
                    <col></col>
                </colgroup>
                <Table.Header>
                    <Table.HeaderCell>
                        {translate('akeneo_connectivity.connection.webhook.event_logs.list.headers.datetime')}
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                        {translate('akeneo_connectivity.connection.webhook.event_logs.list.headers.level')}
                    </Table.HeaderCell>
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
                    {isLoading &&
                        [...Array(25)].map((_, index) => (
                            <LoadingRow key={index}>
                                <LoadingCell colSpan={3} />
                            </LoadingRow>
                        ))}
                </Table.Body>
            </Table>
            {isSearchActive && total === 0 && <NoEventLogsWithThoseFilters />}
        </>
    );
};
