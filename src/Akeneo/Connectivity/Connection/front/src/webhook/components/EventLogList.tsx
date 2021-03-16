import {ArrowRightIcon, ClockIcon, DateIcon, GraphIllustration, Information, Table} from 'akeneo-design-system';
import React, {FC, useRef} from 'react';
import {NoEventLogs} from './NoEventLogs';
import {Translate} from '../../shared/translate';
import styled from 'styled-components';
import {theme} from '../../common/styled-with-theme';
import useInfiniteEventSubscriptionLogs from '../hooks/api/use-infinite-event-subscription-logs';
import {EventLogBadge} from './EventLogBadge';
import {useDateFormatter} from '../../shared/formatter/use-date-formatter';

const ExtraSmallColumnHeaderCell = styled(Table.HeaderCell)`
    width: 125px;
`;
const SmallColumnHeaderCell = styled(Table.HeaderCell)`
    width: 220px;
`;
const StyledDateIcon = styled(DateIcon)`
    vertical-align: text-top;
`;
const StyledClockIcon = styled(ClockIcon)`
    vertical-align: text-top;
`;
const DatetimeContainer = styled.div`
    padding-left: 15px;
    display: flex;
    flex-direction: column;
`;
const Box = styled.span`
    margin-left: 5px;
    margin-top: 2px;
`;
const TimeContainer = styled.span`
    margin-top: 5px;
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
    const dateFormatter = useDateFormatter();

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
                        <Table.Row key={index} onClick={() => undefined}>
                            <Table.Cell>
                                <ArrowRightIcon />
                                <DatetimeContainer>
                                    <div>
                                        <StyledDateIcon size={16} color={theme.color.grey100} />
                                        <Box>
                                            {dateFormatter(timestamp * 1000, {
                                                year: 'numeric',
                                                month: '2-digit',
                                                day: '2-digit',
                                            })}
                                        </Box>
                                    </div>
                                    <TimeContainer>
                                        <StyledClockIcon size={16} color={theme.color.grey100} />
                                        <Box>
                                            {dateFormatter(timestamp * 1000, {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                            })}
                                        </Box>
                                    </TimeContainer>
                                </DatetimeContainer>
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
            <br />
        </>
    );
};
