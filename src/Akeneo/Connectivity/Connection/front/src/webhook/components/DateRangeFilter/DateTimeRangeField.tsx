import {Field} from 'akeneo-design-system';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../shared/translate';
import {DateTimeInput} from './DateTimeInput';

type Timestamp = number;
type Props = {
    min?: Timestamp;
    max?: Timestamp;
    start?: Timestamp;
    end?: Timestamp;
    onChange: (start?: Timestamp, end?: Timestamp) => void;
};

export const DateTimeRangeField: FC<Props> = ({min, max, start, end, onChange}) => {
    const translate = useTranslate();

    return (
        <FlexContainer>
            <Field label={translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.from')}>
                <DateTimeInput value={start} min={min} max={end || max} onChange={start => onChange(start, end)} />
            </Field>
            <Field label={translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.to')}>
                <DateTimeInput value={end} min={start || min} max={max} onChange={end => onChange(start, end)} />
            </Field>
        </FlexContainer>
    );
};

const FlexContainer = styled.div`
    display: flex;
    flex-direction: column;
    gap: 1rem;
`;
