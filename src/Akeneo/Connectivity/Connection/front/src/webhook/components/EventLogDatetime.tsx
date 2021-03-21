import React, {FC} from 'react';
import {ClockIcon, DateIcon} from 'akeneo-design-system';
import {theme} from '../../common/styled-with-theme';
import {useDateFormatter} from '../../shared/formatter/use-date-formatter';
import styled from 'styled-components';

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

const EventLogDatetime: FC<{timestamp: number}> = ({timestamp}) => {
    const dateFormatter = useDateFormatter();

    return (
        <DatetimeContainer>
            <div>
                <StyledDateIcon size={16} color={theme.color.grey100} />
                <Box>
                    {dateFormatter(new Date(timestamp), {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                    })}
                </Box>
            </div>
            <TimeContainer>
                <StyledClockIcon size={16} color={theme.color.grey100} />
                <Box>
                    {dateFormatter(new Date(timestamp), {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    })}
                </Box>
            </TimeContainer>
        </DatetimeContainer>
    );
};

export default EventLogDatetime;
