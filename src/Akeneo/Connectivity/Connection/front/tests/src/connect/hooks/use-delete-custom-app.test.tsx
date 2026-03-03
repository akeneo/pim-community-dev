import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses, ReactQueryWrapper as wrapper} from '../../../test-utils';
import {setLogger} from 'react-query';
import {useDeleteCustomApp} from '@src/connect/hooks/use-delete-custom-app';
import {act, renderHook} from '@testing-library/react-hooks';

setLogger({
    log: () => null,
    warn: () => null,
    error: () => null, // explicit error generation triggers react query to log the error
});

test('it successfully deletes the custom app', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId': {
            json: {},
            status: 200,
        },
    });

    const {result} = renderHook(() => useDeleteCustomApp('appId'), {wrapper});

    const onSuccess = jest.fn();
    const onError = jest.fn();

    await act(async () => {
        try {
            await result.current();
            onSuccess();
        } catch (e) {
            onError();
        }
    });

    expect(fetchMock).toHaveBeenCalledWith(
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId',
        expect.objectContaining({
            method: 'DELETE',
        })
    );

    expect(onSuccess).toHaveBeenCalled();
    expect(onError).not.toHaveBeenCalled();

    done();
});

test('it fails with an error', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId': {
            json: {},
            status: 500,
        },
    });

    const {result} = renderHook(() => useDeleteCustomApp('appId'), {wrapper});

    const onSuccess = jest.fn();
    const onError = jest.fn();

    await act(async () => {
        try {
            await result.current();
            onSuccess();
        } catch (e) {
            onError(e);
        }
    });

    expect(fetchMock).toHaveBeenCalledWith(
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId',
        expect.objectContaining({
            method: 'DELETE',
        })
    );

    expect(onSuccess).not.toHaveBeenCalled();
    expect(onError).toHaveBeenCalledWith('500 Internal Server Error');

    done();
});
