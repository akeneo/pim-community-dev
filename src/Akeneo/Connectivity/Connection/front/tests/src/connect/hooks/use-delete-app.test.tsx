import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';
import {useDeleteApp} from '@src/connect/hooks/use-delete-app';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it deletes the connected app', async done => {
    const {result} = renderHook(() => useDeleteApp('connectionCodeA'));
    await result.current();
    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_apps_rest_delete?connectionCode=connectionCodeA', {
        method: 'DELETE',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    done();
});

test('it rejects when the connected app could not be deleted', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_delete?connectionCode=connectionCodeA': {
            status: 500,
            json: {},
        },
    });

    const {result} = renderHook(() => useDeleteApp('connectionCodeA'));
    await expect(result.current()).rejects.toEqual('500 Internal Server Error');
    done();
});
