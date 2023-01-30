import {validateFamilyProperty} from '../validateFamilyProperty';
import {AbbreviationType, FamilyCodeProperty, Operator, PROPERTY_NAMES} from '../../models';

describe('validateFamilyProperty', () => {
  it('should add a violation when no process type is filled', () => {
    const familyProperty: FamilyCodeProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {type: null},
    };
    const violations = validateFamilyProperty(familyProperty, 'path');
    expect(violations).toEqual([
      {
        message: 'Family code abbreviation type must be filled',
        path: 'path',
      },
    ]);
  });

  it('should add a violation when process is truncate but no operator nor value is given', () => {
    const familyProperty: FamilyCodeProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {type: AbbreviationType.TRUNCATE},
    };
    const violations = validateFamilyProperty(familyProperty, 'path');
    expect(violations).toEqual([
      {
        message: 'The values must be filled',
        path: 'path',
      },
    ]);

    const violations2 = validateFamilyProperty(
      {...familyProperty, process: {...familyProperty.process, operator: Operator.EQUAL}},
      'path'
    );
    expect(violations2).toEqual([
      {
        message: 'The values must be filled',
        path: 'path',
      },
    ]);

    const violations3 = validateFamilyProperty(
      {...familyProperty, process: {...familyProperty.process, value: 3}},
      'path'
    );
    expect(violations3).toEqual([
      {
        message: 'The values must be filled',
        path: 'path',
      },
    ]);
  });
});
