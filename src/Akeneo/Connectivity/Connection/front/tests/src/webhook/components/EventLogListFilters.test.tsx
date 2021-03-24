import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../test-utils';
import {EventLogListFilters} from '@src/webhook/components/EventLogListFilters';

test('It displays the filters for the event log list.', () => {
    renderWithProviders(
        <EventLogListFilters
            filters={{levels: []}}
            onChange={jest.fn()}
            total={42}
        />
    );

    const title = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.title');
    const countTitle = screen.getByText(
        'akeneo_connectivity.connection.webhook.event_logs.list.search.total',
        {exact: false}
    );
    const count = screen.getByText('42', {exact: false});
    const searchLevelTitle = screen.getByText(
        'akeneo_connectivity.connection.webhook.event_logs.list.search.level',
        {exact: false}
    );
    expect(title).toBeInTheDocument();
    expect(countTitle).toBeInTheDocument();
    expect(count).toBeInTheDocument();
    expect(searchLevelTitle).toBeInTheDocument();
});

test('it does not display the total if it is undefined.', () => {
    renderWithProviders(
        <EventLogListFilters
            filters={{levels: []}}
            onChange={jest.fn()}
        />
    );

    const title = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.title');
    const countTitle = screen.queryByText('akeneo_connectivity.connection.webhook.event_logs.list.search.total');
    expect(countTitle).not.toBeInTheDocument();
    expect(title).toBeInTheDocument();
});
