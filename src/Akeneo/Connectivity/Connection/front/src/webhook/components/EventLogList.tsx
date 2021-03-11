import {
    ArrowRightIcon,
    Badge,
    ClockIcon,
    DateIcon,
    GraphIllustration,
    Information,
    Level,
    Table
} from 'akeneo-design-system';
import React, {FC, useContext, useRef} from 'react';
import {NoEventLogs} from './NoEventLogs';
import {Translate} from '../../shared/translate';
import {UserContext} from '../../shared/user';
import styled from 'styled-components';
import {theme} from '../../common/styled-with-theme';
import {EventSubscriptionLogLevel} from '../model/EventSubscriptionLogLevel';
import useInfiniteEventSubscriptionLogs from '../hooks/api/use-infinite-event-subscription-logs';

const ExtraSmallColumnHeaderCell = styled(Table.HeaderCell)`
    width: 9%;
`;
const SmallColumnHeaderCell = styled(Table.HeaderCell)`
    width: 15%;
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
    overflow: hidden
`;

export const EventLogList: FC<{connectionCode: string}> = ({connectionCode}) => {
    const userLocale = useContext(UserContext).get('uiLocale').replace('_', '-');
    const scrollContainer = useRef(null);
    const {logs, page, total} = useInfiniteEventSubscriptionLogs(connectionCode, scrollContainer);

    if (page === 0) {
        return null;
    }

    if (total === 0) {
        return <NoEventLogs />;
    }
    const defineBadgeLevel = (level: EventSubscriptionLogLevel): Level => {
        switch (level) {
            case EventSubscriptionLogLevel.WARNING:
                return 'warning';
            case EventSubscriptionLogLevel.ERROR:
                return 'danger';
            case EventSubscriptionLogLevel.INFO:
                return 'primary';
            case EventSubscriptionLogLevel.NOTICE:
                return 'tertiary';
        }
    };

    return (
        <>
            <Information
                illustration={<GraphIllustration/>}
                title={
                    <Translate
                        id={'akeneo_connectivity.connection.event_logs.info.logs_total'}
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
                        <Translate id={'akeneo_connectivity.connection.event_logs.headers.datetime'} />
                    </SmallColumnHeaderCell>
                    <ExtraSmallColumnHeaderCell>
                        <Translate id={'akeneo_connectivity.connection.event_logs.headers.level'} />
                    </ExtraSmallColumnHeaderCell>
                    <Table.HeaderCell>
                        <Translate id={'akeneo_connectivity.connection.event_logs.headers.message'} />
                    </Table.HeaderCell>
                </Table.Header>
                <Table.Body ref={scrollContainer}>
                    {logs.map(({timestamp, level, message, context}, index) => (
                        <Table.Row key={index} onClick={() => undefined}>
                            <Table.Cell>
                                <ArrowRightIcon/>
                                <DatetimeContainer>
                                    <div>
                                        <StyledDateIcon size={16} color={theme.color.grey100} />
                                        <Box>
                                            {new Intl.DateTimeFormat(userLocale, {
                                                year: 'numeric',
                                                month: '2-digit',
                                                day: '2-digit',
                                            }).format(new Date(timestamp))}
                                        </Box>
                                    </div>
                                    <TimeContainer>
                                        <StyledClockIcon size={16} color={theme.color.grey100} />
                                        <Box>
                                            {new Intl.DateTimeFormat(userLocale, {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                            }).format(new Date(timestamp))}
                                        </Box>
                                    </TimeContainer>
                                </DatetimeContainer>
                            </Table.Cell>
                            <Table.Cell>
                                <Badge level={defineBadgeLevel(level)}>{level.toUpperCase()}</Badge>
                            </Table.Cell>
                            <Table.Cell>
                                <MessageContainer>{message}</MessageContainer>
                                <ContextContainer>{JSON.stringify(context)}</ContextContainer>
                            </Table.Cell>
                        </Table.Row>
                    ))}
                </Table.Body>
            </Table>
            <br/>
        </>
    );
};
