jest.unmock('./useSaveCriteria');

import {renderHook, act} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {waitFor} from '@testing-library/dom';
import {useSaveCriteria} from './useSaveCriteria';
import fetchMock from 'jest-fetch-mock';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it only calls onSuccess when saving is ok', async () => {
    fetchMock.mockResponseOnce('');

    const onSuccess = jest.fn();
    const onError = jest.fn();

    const {result} = renderHook(() => useSaveCriteria('c00a6ef5-da23-4dbe-83a2-98ccc7075890', onSuccess, onError), {
        wrapper: ReactQueryWrapper,
    });

    await waitFor(() => {
        return !result.current.isLoading;
    });

    act(() => result.current.mutate([]));

    await waitFor(() => {
        expect(onSuccess).toHaveBeenCalled();
    });

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/c00a6ef5-da23-4dbe-83a2-98ccc7075890/save-criteria',
        expect.objectContaining({
            body: JSON.stringify([]),
            method: 'POST',
        })
    );
    expect(onSuccess).toHaveBeenCalledTimes(1);
    expect(onError).not.toHaveBeenCalled();
});

test('it calls onSuccess then onError when saving is on error', async () => {
    fetchMock.mockResponseOnce('not found', {
        status: 404,
        statusText: 'not found',
    });

    const onSuccess = jest.fn();
    const onError = jest.fn();

    const {result} = renderHook(() => useSaveCriteria('c00a6ef5-da23-4dbe-83a2-98ccc7075890', onSuccess, onError), {
        wrapper: ReactQueryWrapper,
    });

    await waitFor(() => {
        return !result.current.isLoading;
    });

    act(() => result.current.mutate([]));

    await waitFor(() => {
        expect(onError).toHaveBeenCalled();
    });

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/c00a6ef5-da23-4dbe-83a2-98ccc7075890/save-criteria',
        expect.objectContaining({
            body: JSON.stringify([]),
            method: 'POST',
        })
    );
    expect(onSuccess).toHaveBeenCalledTimes(1);
    expect(onError).toHaveBeenCalledTimes(1);
});
