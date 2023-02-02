import {validateFamilyProperty} from '../validateFamilyProperty';
import {AbbreviationType, FamilyProperty, Operator, PROPERTY_NAMES} from '../../models';

describe('validateFamilyProperty', () => {
  it('should add a violation when no process type is filled', () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {type: null},
    };
    const violations = validateFamilyProperty(familyProperty, 'path');
    expect(violations).toEqual([
      {
        message: 'The empty values must be filled',
        path: 'path',
      },
    ]);
  });

  it('should add a violation when process is truncate but no operator nor value is given', () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {type: AbbreviationType.TRUNCATE},
    };
    const violations = validateFamilyProperty(familyProperty, 'path');
    expect(violations).toEqual([
      {
        message: 'The empty values must be filled',
        path: 'path',
      },
    ]);

    const violations2 = validateFamilyProperty(
      {...familyProperty, process: {...familyProperty.process, operator: Operator.EQUALS}},
      'path'
    );
    expect(violations2).toEqual([
      {
        message: 'The empty values must be filled',
        path: 'path',
      },
    ]);

    const violations3 = validateFamilyProperty(
      {...familyProperty, process: {...familyProperty.process, value: 3}},
      'path'
    );
    expect(violations3).toEqual([
      {
        message: 'The empty values must be filled',
        path: 'path',
      },
    ]);
  });

  it('should add a violation when process is truncate but the value is incorrect', () => {
    const violations1 = validateFamilyProperty(
      {type: PROPERTY_NAMES.FAMILY, process: {type: AbbreviationType.TRUNCATE, operator: Operator.EQUALS, value: 0}},
      'path'
    );

    expect(violations1).toEqual([
      {
        message: 'Please choose a number between 1 and 5',
        path: 'path',
      },
    ]);

    const violations2 = validateFamilyProperty(
      {type: PROPERTY_NAMES.FAMILY, process: {type: AbbreviationType.TRUNCATE, operator: Operator.EQUALS, value: 6}},
      'path'
    );

    expect(violations2).toEqual([
      {
        message: 'Please choose a number between 1 and 5',
        path: 'path',
      },
    ]);
  });
});
