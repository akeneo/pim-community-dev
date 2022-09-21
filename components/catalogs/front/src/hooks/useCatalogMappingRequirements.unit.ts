jest.unmock('./useCatalogMappingRequirements');

import {ReactQueryWrapper} from '../../tests/ReactQueryWrapper';
import {renderHook} from '@testing-library/react-hooks';
import {useCatalog} from './useCatalog';
import {useCatalogMappingRequirements} from './useCatalogMappingRequirements';

test('it returns null', async () => {
    renderHook(() => useCatalog('123e4567-e89b-12d3-a456-426614174000'));

    const {result, waitForNextUpdate} = renderHook(
        () => useCatalogMappingRequirements('123e4567-e89b-12d3-a456-426614174000'),
        {
            wrapper: ReactQueryWrapper,
        }
    );

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
        data: null,
        error: null,
    });
});
