jest.unmock('./useFindAttributeCriterionByType');

import {renderHook} from '@testing-library/react-hooks';
import {useFindAttributeCriterionByType} from './useFindAttributeCriterionByType';
import {AnyAttributeCriterion} from '../models/Criterion';
import AttributeIdentifierCriterion from '../criteria/AttributeIdentifierCriterion';
import AttributeTextCriterion from '../criteria/AttributeTextCriterion';
import AttributeTextareaCriterion from '../criteria/AttributeTextareaCriterion';
import AttributeSimpleSelectCriterion from '../criteria/AttributeSimpleSelectCriterion';
import AttributeMultiSelectCriterion from '../criteria/AttributeMultiSelectCriterion';
import AttributeNumberCriterion from '../criteria/AttributeNumberCriterion';
import AttributeBooleanCriterion from '../criteria/AttributeBooleanCriterion';
import AttributeMeasurementCriterion from '../criteria/AttributeMeasurementCriterion';
import AttributeDateCriterion from '../criteria/AttributeDateCriterion';

const critera: [string, AnyAttributeCriterion][] = [
    ['pim_catalog_identifier', AttributeIdentifierCriterion],
    ['pim_catalog_text', AttributeTextCriterion],
    ['pim_catalog_textarea', AttributeTextareaCriterion],
    ['pim_catalog_simpleselect', AttributeSimpleSelectCriterion],
    ['pim_catalog_multiselect', AttributeMultiSelectCriterion],
    ['pim_catalog_number', AttributeNumberCriterion],
    ['pim_catalog_boolean', AttributeBooleanCriterion],
    ['pim_catalog_metric', AttributeMeasurementCriterion],
    ['pim_catalog_date', AttributeDateCriterion],
];

test.each(critera)('it returns a criterion when searching for "%s"', (field, criterion) => {
    const {result} = renderHook(() => useFindAttributeCriterionByType());

    expect(result.current(field)).toMatchObject(criterion);
});

test('it throws when searching for an unknown type', () => {
    const {result} = renderHook(() => useFindAttributeCriterionByType());

    expect(() => result.current('foo')).toThrow();
});
