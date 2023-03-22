jest.unmock('./useMeasurements');

import {renderHook} from '@testing-library/react-hooks';
import {useMeasurements} from './useMeasurements';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify([
            {
                code: 'cm',
                label: 'centimeters',
            },
            {
                code: 'mm',
                label: 'millimeters',
            },
        ])
    );

    const {result, waitForNextUpdate} = renderHook(() => useMeasurements('length'), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/measurement-families/length/units?locale=en_US',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'cm',
                label: 'centimeters',
            },
            {
                code: 'mm',
                label: 'millimeters',
            },
        ],
        error: null,
    });
});

test('it returns an empty array when no code provided', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useMeasurements(null), {wrapper: ReactQueryWrapper});

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [],
        error: null,
    });
});
