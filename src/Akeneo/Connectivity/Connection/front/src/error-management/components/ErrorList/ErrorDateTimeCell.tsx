import React, {FC} from 'react';
import {css} from 'styled-components';
import styled from '../../../common/styled-with-theme';
import {useDateFormatter} from '../../../shared/formatter/use-date-formatter';
import {ClockIcon, DateIcon, getColor} from 'akeneo-design-system';

type Props = {
    timestamp: number;
};

const useFormatTimestampToDate = () => {
    const formatDateTime = useDateFormatter();
    return (timestamp: number) =>
        formatDateTime(timestamp, {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        });
};

const useFormatTimestampToTime = () => {
    const formatDateTime = useDateFormatter();
    return (timestamp: number) =>
        formatDateTime(timestamp, {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
};

const ErrorDateTimeCell: FC<Props> = ({timestamp}) => {
    const formatTimestampToDate = useFormatTimestampToDate();
    const formatTimestampToTime = useFormatTimestampToTime();

    return (
        <Container collapsing>
            <DateTimeRow>
                <Calendar />
                <DateTimeText>{formatTimestampToDate(timestamp)}</DateTimeText>
            </DateTimeRow>
            <DateTimeRow>
                <Clock />
                <DateTimeText>{formatTimestampToTime(timestamp)}</DateTimeText>
            </DateTimeRow>
        </Container>
    );
};

const Container = styled.td<{collapsing?: boolean}>`
    border-bottom: 1px solid ${({theme}) => theme.color.grey60};
    color: ${({theme}) => theme.color.grey120};
    padding: 15px 20px;

    ${({collapsing}) =>
        collapsing &&
        css`
            width: 1px;
            white-space: nowrap;
        `}
`;

const DateTimeRow = styled.div`
    line-height: ${({theme}) => theme.fontSize.default};
    color: ${({theme}) => theme.color.grey140};
    padding: 5px 0px;
`;

const DateTimeText = styled.span`
    margin-left: 4px;
    vertical-align: text-bottom;
`;

const Calendar = styled(DateIcon)`
    width: 24px;
    height: 24px;
    vertical-align: middle;
    color: ${getColor('grey', 100)};
`;

const Clock = styled(ClockIcon)`
    width: 24px;
    height: 24px;
    vertical-align: middle;
    color: ${getColor('grey', 100)};
`;

export {ErrorDateTimeCell};
