import {TextInput} from 'akeneo-design-system';
import {DateTime} from 'luxon';
import React, {FC, useEffect, useState} from 'react';
import styled from 'styled-components';
import {useUser} from '../../../shared/user';

const DATE_INPUT_FORMAT = 'yyyy-LL-dd';
const TIME_INPUT_FORMAT = 'HH:mm';

const DATE_INPUT_PATTERN = /^\d{4}-\d{2}-\d{2}$/;
const TIME_INPUT_PATTERN = /^\d{2}:\d{2}$/;

const timestampToZonedInputDateString = (timeZone: string, timestamp?: Timestamp) =>
    (timestamp && DateTime.fromSeconds(timestamp, {zone: timeZone}).toFormat(DATE_INPUT_FORMAT)) || '';

const timestampToZonedInputTimeString = (timeZone: string, timestamp?: Timestamp) =>
    (timestamp && DateTime.fromSeconds(timestamp, {zone: timeZone}).toFormat(TIME_INPUT_FORMAT)) || '';

type Timestamp = number;
type Props = {
    value?: Timestamp;
    min?: Timestamp;
    max?: Timestamp;
    onChange: (value?: Timestamp) => void;
    onError: (error: string) => void;
};

export const DateTimeInput: FC<Props> = ({value, min, max, onChange, onError}) => {
    const {timeZone} = useUser();

    const [values, setValues] = useState({
        dateString: timestampToZonedInputDateString(timeZone, value),
        timeString: timestampToZonedInputTimeString(timeZone, value),
    });
    useEffect(() => {
        setValues({
            dateString: timestampToZonedInputDateString(timeZone, value),
            timeString: timestampToZonedInputTimeString(timeZone, value),
        });
    }, [timeZone, value]);

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

        // Use the current time if it's not defined.
        if (timeString === '') {
            timeString = timestampToZonedInputTimeString(timeZone, DateTime.utc().toSeconds());
        }
        if (false === TIME_INPUT_PATTERN.test(timeString)) {
            setErrors(errors => ({...errors, time: true}));
            return;
        }

        const newZonedDateTime = DateTime.fromISO(`${dateString}T${timeString}`, {zone: timeZone});
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
                value={values.dateString}
                min={timestampToZonedInputDateString(timeZone, min)}
                max={timestampToZonedInputDateString(timeZone, max)}
                placeholder={timestampToZonedInputDateString(timeZone, DateTime.utc().toSeconds())}
                onChange={dateString => {
                    setValues(values => ({...values, dateString}));
                    handleChange(dateString, values.timeString);
                }}
                invalid={errors.date}
                aria-label='Date'
            />
            <TextInput
                type='time'
                value={values.timeString}
                placeholder={timestampToZonedInputTimeString(timeZone, DateTime.utc().toSeconds())}
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
