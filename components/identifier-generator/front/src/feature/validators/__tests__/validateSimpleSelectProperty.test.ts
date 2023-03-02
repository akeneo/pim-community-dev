import {validateSimpleSelectProperty} from '../validateSimpleSelectProperty';
import {AbbreviationType, PROPERTY_NAMES, SimpleSelectProperty} from '../../models';

describe('validateSimpleSelectProperty', () => {
  it('returns a violation when process is empty', () => {
    const simpleSelectProperty: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'color',
      process: {
        type: null,
      },
    };
    const violations = validateSimpleSelectProperty(simpleSelectProperty, 'path');
    expect(violations).toEqual([
      {
        path: 'path',
        message: 'The empty values must be filled',
      },
    ]);
  });

  it('returns a violation when process is truncate and operator is empty', () => {
    const simpleSelectProperty: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'color',
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: null,
        value: 3,
      },
    };
    const violations = validateSimpleSelectProperty(simpleSelectProperty, 'path');
    expect(violations).toEqual([
      {
        path: 'path',
        message: 'The empty values must be filled',
      },
    ]);
  });
});
