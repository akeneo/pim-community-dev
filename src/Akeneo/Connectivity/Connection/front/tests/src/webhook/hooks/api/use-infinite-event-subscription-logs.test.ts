import {renderHook} from '@testing-library/react-hooks';
import useInfiniteEventSubscriptionLogs from '@src/webhook/hooks/api/use-infinite-event-subscription-logs';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';
import {mockFetchResponses} from '../../../../test-utils';

beforeEach(() => {
    document.body.innerHTML = `
    <div>
        <div id='content'></div>
    </div>
    `;
});

test('The first logs are fetched on mount', async () => {
    const ref = {current: document.getElementById('content')};

    mockFetchResponses({
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
                total: 1,
                search_after: 'search_after_1',
            },
        },
    });

    const filters = {
        levels: [
            EventSubscriptionLogLevel.INFO,
            EventSubscriptionLogLevel.NOTICE,
            EventSubscriptionLogLevel.WARNING,
            EventSubscriptionLogLevel.ERROR,
        ],
        text: '',
        dateTime: {},
    };

    const {waitForNextUpdate, result, unmount} = renderHook(() =>
        useInfiniteEventSubscriptionLogs('alkemics', filters, ref)
    );

    expect(result.current).toEqual({
        logs: [],
        total: undefined,
        page: 0,
        maxScrollReached: false,
        endScrollReached: false,
        isLoading: true,
        isInitialized: false,
    });

    await waitForNextUpdate();

    expect(result.current).toEqual({
        logs: [
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
        total: 1,
        page: 1,
        maxScrollReached: false,
        endScrollReached: false,
        isLoading: false,
        isInitialized: true,
    });

    unmount();
});
