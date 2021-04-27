import {Field} from 'akeneo-design-system';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../shared/translate';
import {DateTimeInput} from './DateTimeInput';

type Timestamp = number;
type Props = {
    value: {
        start?: Timestamp;
        end?: Timestamp;
    };
    onChange: (value: {start?: Timestamp; end?: Timestamp}) => void;
};

export const DateTimeRangeField: FC<Props> = ({value, onChange}) => {
    const translate = useTranslate();

    return (
        <FlexContainer>
            <Field label={translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.from')}>
                <DateTimeInput
                    value={value.start}
                    defaultTime='00:00'
                    onChange={start => onChange({...value, start: start || undefined})}
                />
            </Field>
            <Field label={translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.to')}>
                <DateTimeInput
                    value={value.end}
                    defaultTime='23:59'
                    onChange={end => onChange({...value, end: end || undefined})}
                />
            </Field>
        </FlexContainer>
    );
};

const FlexContainer = styled.div`
    display: flex;
    flex-direction: column;
    gap: 1rem;
`;
