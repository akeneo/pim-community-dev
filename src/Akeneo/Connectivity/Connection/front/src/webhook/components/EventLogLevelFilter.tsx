import {Checkbox, Dropdown, SwitcherButton} from 'akeneo-design-system';
import React, {FC, useCallback} from 'react';
import {useTranslate} from '../../shared/translate';
import {EventSubscriptionLogLevel} from '../model/EventSubscriptionLogLevel';
import styled from 'styled-components';
import {useToggleState} from '../../shared/hooks/useToggleState';

const LEVELS = [
    EventSubscriptionLogLevel.INFO,
    EventSubscriptionLogLevel.NOTICE,
    EventSubscriptionLogLevel.WARNING,
    EventSubscriptionLogLevel.ERROR,
];

const Container = styled.div`
    position: relative;
`;

export const EventLogLevelFilter: FC<{
    levels: EventSubscriptionLogLevel[];
    onChange: (levels: EventSubscriptionLogLevel[]) => void;
}> = ({levels, onChange}) => {
    const translate = useTranslate();
    const translateLevel = (level: EventSubscriptionLogLevel) =>
        translate('akeneo_connectivity.connection.webhook.event_logs.level.' + level);
    const [isOpen, open, close] = useToggleState(false);
    const handleChange = useCallback(
        (level, checked) => {
            if (checked) {
                onChange([...levels, level].sort((a, b) => LEVELS.indexOf(a) - LEVELS.indexOf(b)));
            } else {
                onChange(levels.filter(v => v !== level));
            }
        },
        [levels, onChange]
    );

    return (
        <Container>
            <SwitcherButton
                label={translate('akeneo_connectivity.connection.webhook.event_logs.list.search.level')}
                onClick={open}
            >
                {levels.length === 0
                    ? translate('akeneo_connectivity.connection.webhook.event_logs.list.search.none')
                    : levels.length === LEVELS.length
                    ? translate('akeneo_connectivity.connection.webhook.event_logs.list.search.all')
                    : levels.map(level => translateLevel(level)).join(', ')}
            </SwitcherButton>
            {isOpen && (
                <Dropdown.Overlay verticalPosition='down' onClose={close}>
                    <Dropdown.Header>
                        <Dropdown.Title>
                            {translate('akeneo_connectivity.connection.webhook.event_logs.list.search.level')}
                        </Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                        {LEVELS.map(level => (
                            <Dropdown.Item key={level}>
                                <Checkbox
                                    checked={levels.includes(level)}
                                    onChange={checked => handleChange(level, checked)}
                                />
                                {translateLevel(level)}
                            </Dropdown.Item>
                        ))}
                    </Dropdown.ItemCollection>
                </Dropdown.Overlay>
            )}
        </Container>
    );
};
