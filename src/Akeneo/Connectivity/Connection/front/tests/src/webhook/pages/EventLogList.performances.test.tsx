import {Index} from '@src/webhook/pages/Index';
import React from 'react';
import {Router} from 'react-router-dom';
import {MockFetchResponses, mockFetchResponses, UserProvider} from '../../../test-utils';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {theme} from '@src/common/styled-with-theme';
import {ThemeProvider} from 'styled-components';
import {createMemoryHistory} from 'history';
import {perf} from 'react-performance-testing';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';
import * as util from 'util';
import {fireEvent} from '@testing-library/dom';

describe('testing events logs page performances', () => {
    const history = createMemoryHistory({
        initialEntries: ['/connect/connection-settings/alkemics/event-logs'],
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
                            foo: 'bar',
                        },
                    },
                ],
                total: 2,
                search_after: 'search_after_1',
            },
        },
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs?connection_code=alkemics&search_after=search_after_1': {
            json: {
                results: [
                    {
                        level: EventSubscriptionLogLevel.INFO,
                        timestamp: 1615741520,
                        connection_code: null,
                        message: 'another log message',
                        context: {
                            foo: 'bar',
                        },
                    },
                ],
                total: 2,
                search_after: 'search_after_2',
            },
        },
        'akeneo_connectivity_connection_events_api_debug_rest_search_event_subscription_logs?connection_code=alkemics&search_after=search_after_2': {
            json: {
                results: [],
                total: 2,
                search_after: 'search_after_3',
            },
        },
    };

    beforeEach(() => {
        fetchMock.resetMocks();
    });

    test('perfs', async () => {
        mockFetchResponses({
            ...fetchConnectionResponses,
            ...fetchEventSubscriptionResponses,
            ...fetchEventSubscriptionLogsResponses,
        });

        const Component = () => {
            return (
                <ThemeProvider theme={theme}>
                    <UserProvider>
                        <Router history={history}>
                            <Index/>
                        </Router>
                    </UserProvider>
                </ThemeProvider>
            );
        };

        const { renderCount, renderTime } = perf(React);

        render(<Component />);

        expect(await screen.findByText('Alkemics')).toBeInTheDocument();
        const row = (await screen.findByText('a log message')).closest('tr') as HTMLTableRowElement;

        fireEvent.scroll(document.body, {target: {scrollY: 100}});

        const row2 = (await screen.findByText('another log message')).closest('tr') as HTMLTableRowElement;

        // console.log(util.inspect(renderCount, false, null, true));
        console.log(JSON.stringify({renderCount, renderTime}));
        // console.log(util.inspect(renderTime, false, null, true));
    });
});
