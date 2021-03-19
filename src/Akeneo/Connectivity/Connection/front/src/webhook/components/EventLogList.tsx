import {ArrowRightIcon, GraphIllustration, Information, Table} from 'akeneo-design-system';
import React, {FC, useRef} from 'react';
import {NoEventLogs} from './NoEventLogs';
import {Translate} from '../../shared/translate';
import styled from 'styled-components';
import useInfiniteEventSubscriptionLogs from '../hooks/api/use-infinite-event-subscription-logs';
import {EventLogBadge} from './EventLogBadge';
import EventLogDatetime from './EventLogDatetime';
import ExpandableTableRow from '../../common/components/ExpandableTableRow';
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

export const EventLogList: FC<{connectionCode: string}> = ({connectionCode}) => {
    const scrollContainer = useRef(null);
    const {logs, page, total} = useInfiniteEventSubscriptionLogs(connectionCode, scrollContainer);

    if (page === 0) {
        return null;
    }

    if (total === 0) {
        return <NoEventLogs />;
    }

    return (
        <>
            <Information
                illustration={<GraphIllustration />}
                title={
                    <Translate
                        id={'akeneo_connectivity.connection.webhook.event_logs.list.info.logs_total'}
                        placeholders={{total: total ? total.toString() : '0'}}
                        count={total}
                    />
                }
            >
                {null}
            </Information>
            <Table>
                <Table.Header>
                    <SmallColumnHeaderCell>
                        <Translate id={'akeneo_connectivity.connection.webhook.event_logs.list.headers.datetime'} />
                    </SmallColumnHeaderCell>
                    <ExtraSmallColumnHeaderCell>
                        <Translate id={'akeneo_connectivity.connection.webhook.event_logs.list.headers.level'} />
                    </ExtraSmallColumnHeaderCell>
                    <Table.HeaderCell>
                        <Translate id={'akeneo_connectivity.connection.webhook.event_logs.list.headers.message'} />
                    </Table.HeaderCell>
                </Table.Header>
                <Table.Body ref={scrollContainer}>
                    {logs.map(({timestamp, level, message, context}, index) => (
                        <ExpandableTableRow key={index} contentToExpand={<FormattedJSON>{context}</FormattedJSON>}>
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
                        </ExpandableTableRow>
                    ))}
                </Table.Body>
            </Table>
            <br />
        </>
    );
};
