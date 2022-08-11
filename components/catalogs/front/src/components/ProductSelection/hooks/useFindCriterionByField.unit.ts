jest.unmock('./useFindCriterionByField');

import {renderHook} from '@testing-library/react-hooks';
import {useFindCriterionByField} from './useFindCriterionByField';
import {AnyCriterion} from '../models/Criterion';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';

const criterions: [string, AnyCriterion][] = [
    ['enabled', StatusCriterion],
    ['family', FamilyCriterion],
];

test.each(criterions)('it returns a criterion when searching for "%s"', async (field, criterion) => {
    const {result} = renderHook(() => useFindCriterionByField());

    expect(await result.current(field)).toMatchObject(criterion);
});

test('it throws when searching for an unknown field', async () => {
    const {result} = renderHook(() => useFindCriterionByField());

    await expect(async () => await result.current('foo')).rejects.not.toBeNull();
});
