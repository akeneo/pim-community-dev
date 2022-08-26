jest.unmock('./useFindAttributeCriterionByType');

import {renderHook} from '@testing-library/react-hooks';
import {useFindAttributeCriterionByType} from './useFindAttributeCriterionByType';
import {AnyAttributeCriterion} from '../models/Criterion';
import AttributeTextCriterion from '../criteria/AttributeTextCriterion';
import AttributeSimpleSelectCriterion from '../criteria/AttributeSimpleSelectCriterion';

const critera: [string, AnyAttributeCriterion][] = [
    ['pim_catalog_text', AttributeTextCriterion],
    ['pim_catalog_simpleselect', AttributeSimpleSelectCriterion],
];

test.each(critera)('it returns a criterion when searching for "%s"', (field, criterion) => {
    const {result} = renderHook(() => useFindAttributeCriterionByType());

    expect(result.current(field)).toMatchObject(criterion);
});

test('it throws when searching for an unknown type', () => {
    const {result} = renderHook(() => useFindAttributeCriterionByType());

    expect(() => result.current('foo')).toThrow();
});
