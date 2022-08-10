jest.unmock('./useFindCriterionByField');

import {renderHook} from '@testing-library/react-hooks';
import {useFindCriterionByField} from './useFindCriterionByField';

const criterions: string[] = ['enabled', 'family'];

test.each(criterions)('it returns a criterion when searching for "%s"', async field => {
    const {result} = renderHook(() => useFindCriterionByField());

    expect(await result.current(field)).toMatchObject({
        component: expect.any(Function),
        factory: expect.any(Function),
    });
});

test('it throws when searching for an unknown field', async () => {
    const {result} = renderHook(() => useFindCriterionByField());

    await expect(async () => await result.current('foo')).rejects.not.toBeNull();
});
