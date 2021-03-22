import React, {FC, useCallback, useContext, useRef, useState} from 'react';
import styled from 'styled-components';
import {ArrowRightIcon, ArrowDownIcon, GraphIllustration, Information, Table} from 'akeneo-design-system';
import {NoEventLogs} from './NoEventLogs';
import {useTranslate} from '../../shared/translate';
import {EventLogBadge} from './EventLogBadge';
import EventLogDatetime from './EventLogDatetime';
import useInfiniteEventSubscriptionLogs, {Filters} from '../hooks/api/use-infinite-event-subscription-logs';
import {EventSubscriptionLogLevel} from '../model/EventSubscriptionLogLevel';
import {EventLogListFilters} from './EventLogListFilters';
import ExpandableTableRow, {IsExpanded} from '../../common/components/ExpandableTableRow';
import FormattedJSON from '../../common/components/FormattedJSON';

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
    const [filters, setFilters] = useState<Filters>({
        levels: [
            EventSubscriptionLogLevel.INFO,
            EventSubscriptionLogLevel.NOTICE,
            EventSubscriptionLogLevel.WARNING,
            EventSubscriptionLogLevel.ERROR,
        ],
    });
    const [isSearchActive, setSearchActive] = useState(false);

    const handleChangeFilters = useCallback((filters: Filters) => {
        setFilters(filters);
        setSearchActive(true);
    }, []);

    const {logs, total, isLoading} = useInfiniteEventSubscriptionLogs(connectionCode, filters, scrollContainer);

    if (!isSearchActive && !isLoading && total === 0) {
        return <NoEventLogs/>;
    }

    const title = !isLoading && undefined !== total
        ? translate('akeneo_connectivity.connection.webhook.event_logs.list.info.logs_total', {total: total.toString()}, total)
        : '';

    return (
        <>
            <Information
                illustration={<GraphIllustration/>}
                title={title}
            >
                {null}
            </Information>
            <EventLogListFilters filters={filters} onChange={handleChangeFilters} total={total}/>
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
                                <Arrow/>
                                <EventLogDatetime timestamp={timestamp * 1000}/>
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
            {total === 0 &&
            <NoEventLogs/>
            }
        </>
    );
};
