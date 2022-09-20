import {validateStructure} from '../validateStructure';
import {PROPERTY_NAMES} from '../../models';

describe('StructureValidator', () => {
  it('should not add violation for valid structure', () => {
    expect(
      validateStructure(
        [
          {
            propertyName: PROPERTY_NAMES.FREE_TEXT,
            value: 'AKN',
          },
        ],
        'structure'
      )
    ).toHaveLength(0);
  });

  it('should add a violation when there are no properties', () => {
    expect(validateStructure([], 'structure')).toEqual([
      {path: 'structure', message: 'The structure must contain at least 1 property'},
    ]);
  });
});
