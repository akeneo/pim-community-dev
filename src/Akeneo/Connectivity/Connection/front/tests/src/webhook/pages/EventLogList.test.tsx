import {Index} from '@src/webhook/pages/Index';
import {createMemoryHistory} from 'history';
import React from 'react';
import {Router} from 'react-router-dom';
import {MockFetchResponses, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {screen, within} from '@testing-library/react';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';

describe('testing events logs page', () => {
    const history = createMemoryHistory({
        initialEntries: ['/connections/alkemics/event-logs'],
    });

    const fetchConnectionResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_rest_get?code=alkemics': {
            json: {
                code: 'alkemics',
                label: 'Alkemics',
                image: null,
            },
        },
    };

    const fetchEventSubscriptionResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_webhook_rest_get?code=alkemics': {
            json: {
                event_subscription: {
                    enabled: true,
                },
            },
        },
    };

    const fetchEventSubscriptionLogsResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs?connection_code=alkemics': {
            json: {
                results: [
                    {
                        level: EventSubscriptionLogLevel.INFO,
                        timestamp: 1615741520,
                        connection_code: null,
                        message: 'a log message',
                        context: {
                            foo: "bar",
                        },
                    },
                ],
                total: 1,
                search_after: 'search_after_1',
            },
        },
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs?connection_code=alkemics&search_after=search_after_1': {
            json: {
                results: [],
                total: 1,
                search_after: 'search_after_2',
            },
        },
    };

    beforeEach(() => {
        fetchMock.resetMocks();
    });

    test('renders the events logs page for the "Alkemics" connection', async () => {
        mockFetchResponses({
            ...fetchConnectionResponses,
            ...fetchEventSubscriptionResponses,
            ...fetchEventSubscriptionLogsResponses,
        });

        renderWithProviders(
            <Router history={history}>
                <Index />
            </Router>
        );

        expect(await screen.findByText('Alkemics')).toBeInTheDocument();
        expect(await screen.findByText('akeneo_connectivity.connection.webhook.event_logs.list.info.logs_total?total=1')).toBeInTheDocument();

        const row = (await screen.findByText('a log message')).closest('tr') as HTMLTableRowElement;
        expect(within(row).getByText('03/14/2021')).toBeInTheDocument();
        expect(within(row).getByText('05:05:20 PM')).toBeInTheDocument();
        expect(within(row).getByText('INFO')).toBeInTheDocument();
        expect(within(row).getByText('a log message')).toBeInTheDocument();
        expect(within(row).getByText('{"foo":"bar"}')).toBeInTheDocument();
    });

    test('displays a message when the connection event subscription is not enabled', async () => {
        mockFetchResponses({
            ...fetchConnectionResponses,
            'akeneo_connectivity_connection_webhook_rest_get?code=alkemics': {
                json: {
                    event_subscription: {
                        enabled: false,
                    },
                },
            },
        });

        renderWithProviders(
            <Router history={history}>
                <Index />
            </Router>
        );

        expect(
            await screen.findByText(
                'akeneo_connectivity.connection.webhook.event_logs.event_subscription_disabled.title'
            )
        ).toBeInTheDocument();
    });

    test('displays a message when there is no logs', async () => {
        mockFetchResponses({
            ...fetchConnectionResponses,
            ...fetchEventSubscriptionResponses,
            'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs?connection_code=alkemics': {
                json: {
                    results: [],
                    total: 0,
                },
            },
        });

        renderWithProviders(
            <Router history={history}>
                <Index />
            </Router>
        );

        expect(
            await screen.findByText('akeneo_connectivity.connection.webhook.event_logs.no_event_logs.title')
        ).toBeInTheDocument();
    });
});
