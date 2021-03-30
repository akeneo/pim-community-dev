import {Dropdown, EraseIcon, IconButton, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {DateTime} from 'luxon';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../shared/translate';
import {useUser} from '../../../shared/user';
import {DateTimeRangeField} from './DateTimeRangeField';

type Timestamp = number;
type Props = {
    value: {
        start?: Timestamp;
        end?: Timestamp;
    };
    limit: {
        min: Timestamp;
        max: Timestamp;
    };
    isDirty: boolean;
    onChange: (start?: Timestamp, end?: Timestamp) => void;
    onReset: () => void;
};

export const EventLogDateTimeRangeFilter: FC<Props> = ({value, limit, isDirty, onChange, onReset}) => {
    const translate = useTranslate();
    const {timeZone: zone, locale} = useUser();

    const [isOpen, open, close] = useBooleanState(false);

    const createLabelValue = (start?: Timestamp, end?: Timestamp) => {
        const str = [];
        if (undefined !== start && start !== limit.min) {
            str.push(
                translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.from'),
                DateTime.fromSeconds(start, {zone}).toLocaleString({...DateTime.DATETIME_SHORT, locale})
            );
        }
        if (undefined !== end && end !== limit.min) {
            str.push(
                translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.to'),
                DateTime.fromSeconds(end, {zone}).toLocaleString({...DateTime.DATETIME_SHORT, locale})
            );
        }
        if (str.length > 0) {
            return str.join(' ');
        }

        return translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.all');
    };

    return (
        <Dropdown>
            <SwitcherButton
                label={translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.label')}
                onClick={open}
            >
                {createLabelValue(value.start, value.end)}
            </SwitcherButton>
            {isOpen && (
                <Dropdown.Overlay verticalPosition='down' onClose={close}>
                    <Dropdown.Header>
                        <FlexContainer>
                            <GrowingFlexItem>
                                <Dropdown.Title>
                                    {translate(
                                        'akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.label'
                                    )}
                                </Dropdown.Title>
                            </GrowingFlexItem>
                            {isDirty && (
                                <IconButton
                                    icon={<EraseIcon />}
                                    ghost='borderless'
                                    level='tertiary'
                                    title={translate(
                                        'akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.reset'
                                    )}
                                    onClick={onReset}
                                />
                            )}
                        </FlexContainer>
                    </Dropdown.Header>
                    <DropdownBody>
                        <DateTimeRangeField
                            min={limit.min}
                            max={limit.max}
                            start={value.start}
                            end={value.end}
                            onChange={onChange}
                        />
                    </DropdownBody>
                </Dropdown.Overlay>
            )}
        </Dropdown>
    );
};

const FlexContainer = styled.div`
    display: flex;
    align-items: center;
`;

const GrowingFlexItem = styled.div`
    flex-grow: 1;
`;

const DropdownBody = styled.div`
    margin: 0 20px;
`;
