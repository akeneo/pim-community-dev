import {useCreateTestApp} from '@src/connect/hooks/use-create-test-app';
import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';

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

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_custom_apps_rest_create', {
        body: '{"name":"Test app bynder","activateUrl":"http://any_url.test","callbackUrl":"http://activate.test"}',
        headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
        ],
        method: 'POST',
    });

    done();
});

test('it returns errors when fields are not valid', async done => {
    const expectedTestApp = {
        errors: [
            {propertyPath: 'name', message: 'not_blank'},
            {propertyPath: 'activateUrl', message: 'valid_url'},
            {propertyPath: 'callbackUrl', message: 'not_blank'},
        ],
    };

    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_create: {
            json: expectedTestApp,
            status: 422,
        },
    });

    const {result} = renderHook(() => useCreateTestApp());

    const response = await result.current({
        name: '',
        activateUrl: 'foo',
        callbackUrl: '',
    });

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_custom_apps_rest_create', {
        body: '{"name":"","activateUrl":"foo","callbackUrl":""}',
        headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
        ],
        method: 'POST',
    });

    expect(response.status).toBe(422);

    response.json().then(data => {
        expect(data).toStrictEqual(expectedTestApp);
    });

    done();
});
