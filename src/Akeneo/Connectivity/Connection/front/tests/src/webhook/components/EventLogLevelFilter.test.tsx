import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';
import {renderWithProviders} from '../../../test-utils';
import {EventLogLevelFilter} from '@src/webhook/components/EventLogLevelFilter';
import {fireEvent} from '@testing-library/dom';

test('It display "none" when there is no level selected', () => {
    renderWithProviders(
        <EventLogLevelFilter
            levels={[]}
            onChange={jest.fn()}
        />
    );

    const value = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.none');
    expect(value).toBeInTheDocument();
});

test('It display "all" when all levels are selected', () => {
    const levels: EventSubscriptionLogLevel[] = [
        EventSubscriptionLogLevel.INFO,
        EventSubscriptionLogLevel.NOTICE,
        EventSubscriptionLogLevel.WARNING,
        EventSubscriptionLogLevel.ERROR,
    ];

    renderWithProviders(
        <EventLogLevelFilter
            levels={levels}
            onChange={jest.fn()}
        />
    );

    const value = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.all');
    expect(value).toBeInTheDocument();
});

test('It display a list when not all levels are selected', () => {
    const levels: EventSubscriptionLogLevel[] = [
        EventSubscriptionLogLevel.INFO,
        EventSubscriptionLogLevel.NOTICE,
    ];

    renderWithProviders(
        <EventLogLevelFilter
            levels={levels}
            onChange={jest.fn()}
        />
    );

    const values = [
        'akeneo_connectivity.connection.webhook.event_logs.level.info',
        'akeneo_connectivity.connection.webhook.event_logs.level.notice',
    ];

    const value = screen.getByText(values.join(', '));
    expect(value).toBeInTheDocument();
});

test('It can change the selected levels by opening the dropdown and clicking on one of the checkbox', () => {
    const handleChange = jest.fn();

    renderWithProviders(
        <EventLogLevelFilter
            levels={[]}
            onChange={handleChange}
        />
    );

    const button = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.level', {exact: false}).closest('button') as HTMLButtonElement;
    fireEvent.click(button);
    const levelInfoCheckboxLabel = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.level.info');
    fireEvent.click(levelInfoCheckboxLabel);

    expect(handleChange).toHaveBeenCalledWith([
        EventSubscriptionLogLevel.INFO,
    ]);
});
