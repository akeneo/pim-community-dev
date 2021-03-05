import {Index} from '@src/webhook/pages/Index';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Router} from 'react-router-dom';
import {fetchMockResponseOnce, renderWithProviders} from '../../../test-utils';

describe('testing events logs page', () => {
    const history = createMemoryHistory({
        initialEntries: ['/connections/alkemics/event-logs'],
    });

    const mockFetchConnection = () =>
        fetchMockResponseOnce(
            'akeneo_connectivity_connection_rest_get?code=alkemics',
            JSON.stringify({
                code: 'alkemics',
                label: 'Alkemics',
                image: null,
            })
        );

    const mockFetchEventSubscription = ({enabled} = {enabled: true}) =>
        fetchMockResponseOnce(
            'akeneo_connectivity_connection_webhook_rest_get?code=alkemics',
            JSON.stringify({
                event_subscription: {
                    enabled,
                },
            })
        );

    beforeEach(() => {
        fetchMock.resetMocks();
    });

    test('renders the events logs page for the "Alkemics" connection', async () => {
        mockFetchConnection();
        mockFetchEventSubscription();

        const {findByText} = renderWithProviders(
            <Router history={history}>
                <Index />
            </Router>
        );

        await findByText('Alkemics');
    });

    test('displays a message when the connection event subscription is not enabled', async () => {
        mockFetchConnection();
        mockFetchEventSubscription({enabled: false});

        const {findByText} = renderWithProviders(
            <Router history={history}>
                <Index />
            </Router>
        );

        await findByText('akeneo_connectivity.connection.webhook.event_logs.event_subscription_disabled.title');
    });

    test('displays a message when there is no logs', async () => {
        mockFetchConnection();
        mockFetchEventSubscription();

        const {findByText} = renderWithProviders(
            <Router history={history}>
                <Index />
            </Router>
        );

        await findByText('akeneo_connectivity.connection.webhook.event_logs.no_event_logs.title');
    });
});
