import {useCreateTestApp} from '@src/connect/hooks/use-create-test-app';
import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it creates the test app and returns credentials', async done => {
    const expectedTestAppMessages = [
        {
            clientId: 'string',
            clientSecret: 'string',
        },
    ];

    mockFetchResponses({
        akeneo_connectivity_connection_test_apps_rest_create: {
            json: expectedTestAppMessages,
        },
    });

    const {result} = renderHook(() => useCreateTestApp());

    const testAppMessage = await result.current({
        name: 'Test app bynder',
        activate_url: 'http://any_url.test',
        callback_url: 'http://activate.test',
    });

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_test_apps_rest_create', {
        body: '{"name":"Test app bynder","activate_url":"http://any_url.test","callback_url":"http://activate.test"}',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
        method: 'POST',
    });

    expect(testAppMessage).toStrictEqual(expectedTestAppMessages);
    done();
});
