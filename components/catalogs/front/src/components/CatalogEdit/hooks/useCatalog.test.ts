jest.unmock('./useCatalog');

import {renderHook} from '@testing-library/react-hooks';
import {useCatalog} from './useCatalog';
import {useCatalogQuery} from '../../../hooks/useCatalogQuery';

test('it calls the query', () => {
    renderHook(() => useCatalog('a4ecb5c7-7e80-44a8-baa1-549db0707f79'));

    expect(useCatalogQuery).toHaveBeenCalledWith('a4ecb5c7-7e80-44a8-baa1-549db0707f79');
});
