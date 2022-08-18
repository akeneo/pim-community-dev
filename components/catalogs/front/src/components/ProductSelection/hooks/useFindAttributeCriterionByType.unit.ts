jest.unmock('./useFindAttributeCriterionByType');

import {renderHook} from '@testing-library/react-hooks';
import {useFindAttributeCriterionByType} from './useFindAttributeCriterionByType';

const types: string[] = ['pim_catalog_text', 'pim_catalog_boolean'];

test.each(types)('it returns a criterion when searching for "%s"', field => {
    const {result} = renderHook(() => useFindAttributeCriterionByType());

    expect(result.current(field)).toMatchObject({
        component: expect.any(Function),
        factory: expect.any(Function),
    });
});

test('it throws when searching for an unknown type', () => {
    const {result} = renderHook(() => useFindAttributeCriterionByType());

    expect(() => result.current('foo')).toThrow();
});
