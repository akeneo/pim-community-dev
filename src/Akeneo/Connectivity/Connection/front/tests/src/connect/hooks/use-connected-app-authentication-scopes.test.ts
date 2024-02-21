import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';
import fetchMock from 'jest-fetch-mock';
import {useAuthenticationScopes} from '@src/connect/hooks/use-connected-app-authentication-scopes';

beforeEach(() => {
    jest.clearAllMocks();
    fetchMock.resetMocks();
});

test('it returns loading status and authenticationScopes values', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_connected_app_authentication_scopes?connectionCode=some_connection_code':
            {
                json: ['openid', 'email', 'profile'],
            },
    });

    const {result, waitForNextUpdate} = renderHook(() => useAuthenticationScopes('some_connection_code'));

    expect(result.current).toStrictEqual({
        isLoading: true,
        authenticationScopes: [],
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        authenticationScopes: ['openid', 'email', 'profile'],
    });
});

test('it returns loading status and empty values on fetch error', async () => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_connected_app_authentication_scopes?connectionCode=some_connection_code':
            {
                json: {},
                reject: true,
            },
    });

    const {result, waitForNextUpdate} = renderHook(() => useAuthenticationScopes('some_connection_code'));

    expect(result.current).toStrictEqual({
        isLoading: true,
        authenticationScopes: [],
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        isLoading: false,
        authenticationScopes: [],
    });
});
