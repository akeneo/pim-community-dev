import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../test-utils';
import {EventLogListFilters} from '@src/webhook/components/EventLogListFilters';

test('It displays the filters for the event log list.', () => {
    renderWithProviders(
        <EventLogListFilters filters={{levels: [], text: 'I search', dateTime: {}}} onChange={jest.fn()} total={42} />
    );

    const searchText = screen.getByTestId('event-logs-list-search-text-filter');
    const countTitle = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.total', {
        exact: false,
    });
    const count = screen.getByText('42', {exact: false});
    const searchLevelTitle = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.level', {
        exact: false,
    });
    expect(searchText).toBeInTheDocument();
    expect(searchText.getAttribute('value')).toEqual('I search');
    expect(countTitle).toBeInTheDocument();
    expect(count).toBeInTheDocument();
    expect(searchLevelTitle).toBeInTheDocument();
});

test('it does not display the total if it is undefined.', () => {
    renderWithProviders(
        <EventLogListFilters filters={{levels: [], text: 'I search', dateTime: {}}} onChange={jest.fn()} />
    );

    const searchText = screen.getByTestId('event-logs-list-search-text-filter');
    const countTitle = screen.queryByText('akeneo_connectivity.connection.webhook.event_logs.list.search.total');
    expect(searchText.getAttribute('value')).toEqual('I search');
    expect(countTitle).not.toBeInTheDocument();
    expect(searchText).toBeInTheDocument();
});
