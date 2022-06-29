jest.unmock('./useSaveCatalog');

import fetchMock from 'jest-fetch-mock';
import {act, renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useSaveCatalog} from './useSaveCatalog';

test('it returns a saver that call the API', async () => {
    const {result} = renderHook(() => useSaveCatalog(), {
        wrapper: ReactQueryWrapper,
    });

    await act(async () => {
        await result.current({
            id: 'a4ecb5c7-7e80-44a8-baa1-549db0707f79',
            values: {
                enabled: true,
                product_selection_criteria: [],
            },
        });
    });

    expect(fetchMock).toHaveBeenCalledWith(
        '/rest/catalogs/a4ecb5c7-7e80-44a8-baa1-549db0707f79',
        expect.objectContaining({
            method: 'PATCH',
            body: JSON.stringify({
                enabled: true,
                product_selection_criteria: [],
            }),
        })
    );
});
