import {useCreateTestApp} from '@src/connect/hooks/use-create-test-app';
import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it creates the test app and returns credentials', async done => {
    const {result} = renderHook(() => useCreateTestApp());

    await result.current({
        name: 'Test app bynder',
        activateUrl: 'http://any_url.test',
        callbackUrl: 'http://activate.test',
    });

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_marketplace_rest_test_apps_create', {
        body: '{"name":"Test app bynder","activateUrl":"http://any_url.test","callbackUrl":"http://activate.test"}',
        headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
        ],
        method: 'POST',
    });

    done();
});
