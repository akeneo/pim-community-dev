jest.unmock('./useCountProductsInSelectionCriteria');

import {renderHook} from '@testing-library/react-hooks';
import {useCountProductsInSelectionCriteria} from './useCountProductsInSelectionCriteria';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';

test('it fetches the API response', async () => {
    fetchMock.mockResponseOnce(JSON.stringify(5));
    const productSelectionCriteria = [
        {
            field: 'enabled',
            operator: '=',
            value: true,
        },
    ];

    const {result, waitForNextUpdate} = renderHook(
        () => useCountProductsInSelectionCriteria(productSelectionCriteria),
        {wrapper: ReactQueryWrapper}
    );

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    });

    await waitForNextUpdate();

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/product-selection-criteria/product/count',
        expect.any(Object)
    );
    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: 5,
        error: null,
    });
});
