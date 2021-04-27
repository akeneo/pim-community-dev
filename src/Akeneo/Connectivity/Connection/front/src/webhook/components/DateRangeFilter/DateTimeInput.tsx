import {TextInput} from 'akeneo-design-system';
import {DateTime} from 'luxon';
import React, {FC, useEffect, useState} from 'react';
import styled from 'styled-components';
import {useUser} from '../../../shared/user';

const DATE_INPUT_FORMAT = 'yyyy-LL-dd';
const TIME_INPUT_FORMAT = 'HH:mm';

const DATE_INPUT_PATTERN = /^\d{4}-\d{2}-\d{2}$/;
const TIME_INPUT_PATTERN = /^\d{2}:\d{2}$/;

type Timestamp = number;
type Props = {
    value?: Timestamp;
    onChange: (value: Timestamp | null) => void;
    defaultTime: string;
};

const dateTimeToDateInputString = (datetime: DateTime | null): string => {
    return datetime?.toFormat(DATE_INPUT_FORMAT) || '';
};

const dateTimeToTimeInputString = (datetime: DateTime | null): string => {
    return datetime?.toFormat(TIME_INPUT_FORMAT) || '';
};

const DATE_PLACEHOLDER = dateTimeToDateInputString(DateTime.now());
const TIME_PLACEHOLDER = dateTimeToTimeInputString(DateTime.now());

export const DateTimeInput: FC<Props> = ({value, defaultTime, onChange}) => {
    const {timeZone} = useUser();

    const datetime = undefined === value ? null : DateTime.fromSeconds(value, {zone: timeZone});

    const [values, setValues] = useState<{date: string; time: string}>({
        date: null !== datetime ? datetime.toFormat(DATE_INPUT_FORMAT) : '',
        time: null !== datetime ? datetime.toFormat(TIME_INPUT_FORMAT) : '',
    });

    useEffect(() => {
        if ('' === values.date || '' === values.time) {
            if (undefined !== value) {
                onChange(null);
            }
            return;
        }

        const newZonedDateTime = DateTime.fromISO(`${values.date}T${values.time}`, {zone: timeZone});
        const newTimestamp = newZonedDateTime.toSeconds();
        if (value !== newTimestamp) {
            onChange(newTimestamp);
        }
    }, [onChange, timeZone, value, values]);

    useEffect(() => {
        if (value === undefined) {
            setValues({
                date: '',
                time: '',
            });
        }
    }, [value]);

    const handleDateChange = (date: string) => {
        if (!DATE_INPUT_PATTERN.test(date)) {
            setValues(values => ({
                ...values,
                date: '',
            }));
            return;
        }

        setValues(values => ({
            date,
            time: '' === values.time && undefined !== defaultTime ? defaultTime : values.time,
        }));
    };

    const handleTimeChange = (time: string) => {
        if (!TIME_INPUT_PATTERN.test(time)) {
            setValues(values => ({
                ...values,
                time: undefined !== defaultTime ? defaultTime : '',
            }));
            return;
        }

        setValues(values => ({
            ...values,
            time,
        }));
    };

    return (
        <FlexContainer>
            <TextInput
                type='date'
                value={values.date}
                placeholder={DATE_PLACEHOLDER}
                onChange={handleDateChange}
                aria-label='Date'
                required
            />
            <TextInput
                type='time'
                value={values.time}
                placeholder={TIME_PLACEHOLDER}
                onChange={handleTimeChange}
                aria-label='Time'
                required
            />
        </FlexContainer>
    );
};

const FlexContainer = styled.div`
    min-width: 230px;
    display: flex;
    gap: 10px;
`;
