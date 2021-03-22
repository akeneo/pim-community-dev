import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../test-utils';
import {EventLogListFilters} from '@src/webhook/components/EventLogListFilters';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';

test('it displays information about event log list.', () => {
    renderWithProviders(
        <EventLogListFilters
            filters={{levels: [EventSubscriptionLogLevel.INFO, EventSubscriptionLogLevel.NOTICE]}}
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
    const searchLevel = screen.getByText(
        'akeneo_connectivity.connection.webhook.event_logs.list.search.level',
        {exact: false}
    );
    const infoLevel = screen.getByText(
        'akeneo_connectivity.connection.webhook.event_logs.level.info',
        {exact: false}
    );
    const noticeLevel = screen.getByText(
        'akeneo_connectivity.connection.webhook.event_logs.level.notice',
        {exact: false}
    );

    expect(title).toBeInTheDocument();
    expect(countTitle).toBeInTheDocument();
    expect(count).toBeInTheDocument();
    expect(searchLevel).toBeInTheDocument();
    expect(infoLevel).toBeInTheDocument();
    expect(noticeLevel).toBeInTheDocument();
});

test('it does not display the total if it is undefined.', () => {
    renderWithProviders(
        <EventLogListFilters
            filters={{levels: [EventSubscriptionLogLevel.INFO, EventSubscriptionLogLevel.NOTICE]}}
            onChange={jest.fn()}
        />
    );

    const title = screen.getByText('akeneo_connectivity.connection.webhook.event_logs.list.search.title');
    const countTitle = screen.queryByText('akeneo_connectivity.connection.webhook.event_logs.list.search.total');
    expect(countTitle).not.toBeInTheDocument();
    expect(title).toBeInTheDocument();
});
