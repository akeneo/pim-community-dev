import {renderHook} from '@testing-library/react-hooks';
import {useFeatureFlags} from '@src/shared/feature-flags';
import {useConnectedApp} from '@src/connect/hooks/use-connected-app';
import {useFetchConnectedApp} from '@src/connect/hooks/use-fetch-connected-app';
import {useCallback} from 'react';

jest.mock('@src/shared/feature-flags/use-feature-flags');
jest.mock('@src/connect/hooks/use-fetch-connected-app');

beforeEach(() => {
    jest.clearAllMocks();
});

test('it returns forbidden error with marketplace activate feature flag disabled', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: () => false,
    }));

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApp('someConnectionCode'));

    expect(result.current).toStrictEqual({
        loading: false,
        error: 'FORBIDDEN',
        payload: null,
    });
});

test('it returns loading status then forbidden error when fetching connected app throws not forbidden error', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: () => true,
    }));

    (useFetchConnectedApp as jest.Mock).mockImplementation(() =>
        useCallback(async () => Promise.reject('403 Forbidden'), [])
    );

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApp('someConnectionCode'));

    expect(result.current).toStrictEqual({
        loading: true,
        error: null,
        payload: null,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        loading: false,
        error: 'FORBIDDEN',
        payload: null,
    });
});

test('it returns loading status then not found error when fetching connected app throws not found error', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: () => true,
    }));

    (useFetchConnectedApp as jest.Mock).mockImplementation(() =>
        useCallback(async () => Promise.reject('404 Not Found'), [])
    );

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApp('someConnectionCode'));

    expect(result.current).toStrictEqual({
        loading: true,
        error: null,
        payload: null,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        loading: false,
        error: 'NOT_FOUND',
        payload: null,
    });
});

test('it returns loading status then a connected app', async () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({
        isEnabled: () => true,
    }));

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        activate_url: 'http://example.com/activate',
        is_custom_app: false,
        is_pending: false,
    };

    (useFetchConnectedApp as jest.Mock).mockImplementation(() =>
        useCallback(async () => Promise.resolve(connectedApp), [])
    );

    const {result, waitForNextUpdate} = renderHook(() => useConnectedApp('someConnectionCode'));

    expect(result.current).toStrictEqual({
        loading: true,
        error: null,
        payload: null,
    });

    await waitForNextUpdate();

    expect(result.current).toStrictEqual({
        loading: false,
        error: null,
        payload: connectedApp,
    });
});
