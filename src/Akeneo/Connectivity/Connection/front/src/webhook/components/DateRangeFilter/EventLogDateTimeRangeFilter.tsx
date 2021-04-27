import {Dropdown, EraseIcon, IconButton, SwitcherButton, useBooleanState} from 'akeneo-design-system';
import {DateTime} from 'luxon';
import React, {FC, useCallback} from 'react';
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
    onChange: (value: {start?: Timestamp; end?: Timestamp}) => void;
};

export const EventLogDateTimeRangeFilter: FC<Props> = ({value, onChange}) => {
    const translate = useTranslate();
    const {timeZone: zone, locale} = useUser();

    const [isOpen, open, close] = useBooleanState(false);

    const hasValue = undefined !== value.start || undefined !== value.end;

    const createLabel = (value: {start?: Timestamp; end?: Timestamp}) => {
        const str = [];
        if (undefined !== value.start) {
            str.push(
                translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.from'),
                DateTime.fromSeconds(value.start, {zone}).toLocaleString({...DateTime.DATETIME_SHORT, locale})
            );
        }
        if (undefined !== value.end) {
            str.push(
                translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.to'),
                DateTime.fromSeconds(value.end, {zone}).toLocaleString({...DateTime.DATETIME_SHORT, locale})
            );
        }
        if (str.length > 0) {
            return str.join(' ');
        }

        return translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.all');
    };

    const handleReset = useCallback(() => {
        onChange({
            start: undefined,
            end: undefined,
        });
    }, [onChange]);

    return (
        <Dropdown>
            <SwitcherButton
                label={translate('akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.label')}
                onClick={open}
            >
                {createLabel(value)}
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
                            {hasValue && (
                                <IconButton
                                    icon={<EraseIcon />}
                                    ghost='borderless'
                                    level='tertiary'
                                    title={translate(
                                        'akeneo_connectivity.connection.webhook.event_logs.list.date_range_filter.reset'
                                    )}
                                    onClick={handleReset}
                                />
                            )}
                        </FlexContainer>
                    </Dropdown.Header>
                    <DropdownBody>
                        <DateTimeRangeField value={value} onChange={onChange} />
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
