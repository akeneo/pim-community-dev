jest.unmock('./useFamiliesByCodes');
jest.unmock('./useInfiniteFamilies');

import {Family} from '../models/Family';
import {renderHook} from '@testing-library/react-hooks';
import {useFamiliesByCodes} from './useFamiliesByCodes';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import fetchMock from 'jest-fetch-mock';

test('it returns families', async () => {
    const families: Family[] = [
        {
            code: 'foo',
            label: 'Foo',
        },
        {
            code: 'bar',
            label: 'Bar',
        },
    ];

    fetchMock.mockResponses(JSON.stringify(families), JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useFamiliesByCodes(['foo', 'bar']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: [],
        error: null,
    });

    await waitForNextUpdate();

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: families,
        error: null,
    });
});
