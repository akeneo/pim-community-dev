import {TextInput} from 'akeneo-design-system';
import {DateTime} from 'luxon';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useUser} from '../../../shared/user';

const DATE_INPUT_FORMAT = 'yyyy-LL-dd';
const TIME_INPUT_FORMAT = 'HH:mm';

type Timestamp = number;
type Props = {
    value?: Timestamp;
    min?: Timestamp;
    max?: Timestamp;
    onChange: (value?: Timestamp) => void;
    invalid?: boolean;
};

export const DateTimeInput: FC<Props> = ({value, min, max, onChange, invalid}) => {
    const {timeZone: zone} = useUser();

    const timestampToZonedString = (timestamp: Timestamp, toFormat: string) =>
        DateTime.fromSeconds(timestamp, {zone}).toFormat(toFormat);

    const handleDateChange = (dateString: string) => {
        const newDate = DateTime.fromFormat(dateString, DATE_INPUT_FORMAT, {zone});
        if (undefined === value) {
            onChange(newDate.toSeconds());

            return;
        }

        const newZonedDateTime = DateTime.fromSeconds(value, {zone}).set({
            year: newDate.year,
            month: newDate.month,
            day: newDate.day,
        });
        onChange(newZonedDateTime.toSeconds());
    };

    const handleTimeChange = (timeString: string) => {
        const newTime = DateTime.fromFormat(timeString, TIME_INPUT_FORMAT, {zone});
        if (undefined === value) {
            return;
        }

        const newZonedDateTime = DateTime.fromSeconds(value, {zone}).set({
            hour: newTime.hour,
            minute: newTime.minute,
            second: 0,
        });
        onChange(newZonedDateTime.toSeconds());
    };

    return (
        <FlexContainer>
            <TextInput
                type='date'
                value={(value && timestampToZonedString(value, DATE_INPUT_FORMAT)) || ''}
                min={min && timestampToZonedString(min, DATE_INPUT_FORMAT)}
                max={max && timestampToZonedString(max, DATE_INPUT_FORMAT)}
                onChange={handleDateChange}
                invalid={invalid}
            />
            <TextInput
                type='time'
                value={(value && timestampToZonedString(value, TIME_INPUT_FORMAT)) || ''}
                onChange={handleTimeChange}
                disabled={undefined === value}
                invalid={invalid}
            />
        </FlexContainer>
    );
};

const FlexContainer = styled.div`
    min-width: 230px;
    display: flex;
    gap: 2px;
`;
