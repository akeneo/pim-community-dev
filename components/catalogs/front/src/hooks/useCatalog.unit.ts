jest.unmock('./useCatalog');

import {renderHook} from '@testing-library/react-hooks';
import {useCatalog} from './useCatalog';
import {useCatalogQuery} from './useCatalogQuery';

test('it calls the query', () => {
    renderHook(() => useCatalog('123e4567-e89b-12d3-a456-426614174000'));

    expect(useCatalogQuery).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000');
});
