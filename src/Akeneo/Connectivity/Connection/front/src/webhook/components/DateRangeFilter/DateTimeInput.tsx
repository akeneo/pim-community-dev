import {TextInput} from 'akeneo-design-system';
import {DateTime} from 'luxon';
import React, {FC, useState} from 'react';
import styled from 'styled-components';
import {useUser} from '../../../shared/user';

const DATE_INPUT_FORMAT = 'yyyy-LL-dd';
const TIME_INPUT_FORMAT = 'HH:mm';

const DATE_INPUT_PATTERN = /^\d{4}-\d{2}-\d{2}$/;
const TIME_INPUT_PATTERN = /^\d{2}:\d{2}$/;

type Timestamp = number;
type Props = {
    value?: Timestamp;
    min?: Timestamp;
    max?: Timestamp;
    onChange: (value?: Timestamp) => void;
    onError: (error: string) => void;
};

export const DateTimeInput: FC<Props> = ({value, min, max, onChange, onError}) => {
    const {timeZone: zone} = useUser();

    const timestampToZonedString = (timestamp: Timestamp, toFormat: string) =>
        DateTime.fromSeconds(timestamp, {zone}).toFormat(toFormat);

    const [values, setValues] = useState({dateString: '', timeString: ''});
    const [errors, setErrors] = useState<{date?: boolean; time?: boolean}>({});

    const handleChange = (dateString: string, timeString: string) => {
        setErrors({});

        if (dateString === '' && timeString === '') {
            onChange(undefined);
            return;
        }

        if (dateString === '' || false === DATE_INPUT_PATTERN.test(dateString)) {
            setErrors(errors => ({...errors, date: true}));
            return;
        }
        if (timeString === '' || false === TIME_INPUT_PATTERN.test(timeString)) {
            setErrors(errors => ({...errors, time: true}));
            return;
        }

        const newZonedDateTime = DateTime.fromISO(`${dateString}T${timeString}`, {zone});
        if (!newZonedDateTime.isValid) {
            const error = newZonedDateTime.invalidExplanation as string;
            setErrors({date: true, time: true});
            onError(error);
            return;
        }

        onChange(newZonedDateTime.toSeconds());
    };

    return (
        <FlexContainer>
            <TextInput
                type='date'
                defaultValue={(value && timestampToZonedString(value, DATE_INPUT_FORMAT)) || ''}
                min={min && timestampToZonedString(min, DATE_INPUT_FORMAT)}
                max={max && timestampToZonedString(max, DATE_INPUT_FORMAT)}
                placeholder={timestampToZonedString(DateTime.utc().toSeconds(), DATE_INPUT_FORMAT)}
                onChange={dateString => {
                    setValues(values => ({...values, dateString}));
                    handleChange(dateString, values.timeString);
                }}
                invalid={errors.date}
                aria-label='Date'
            />
            <TextInput
                type='time'
                defaultValue={(value && timestampToZonedString(value, TIME_INPUT_FORMAT)) || ''}
                placeholder={timestampToZonedString(DateTime.utc().toSeconds(), TIME_INPUT_FORMAT)}
                onChange={timeString => {
                    setValues(values => ({...values, timeString}));
                    handleChange(values.dateString, timeString);
                }}
                invalid={errors.time}
                aria-label='Time'
            />
        </FlexContainer>
    );
};

const FlexContainer = styled.div`
    min-width: 230px;
    display: flex;
    gap: 10px;
`;
