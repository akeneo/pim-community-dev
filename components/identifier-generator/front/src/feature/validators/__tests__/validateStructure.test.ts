import {validateStructure} from '../validateStructure';
import {PROPERTY_NAMES, Structure} from '../../models';

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

  it('should add a violation when the property is unknown', () => {
    expect(
      validateStructure(
        [
          {
            propertyName: 'unknown',
          },
        ] as Structure,
        'structure'
      )
    ).toEqual([
      {
        path: 'structure[0]',
        message: 'The property type "unknown" is unknown. Please choose one of the following: free_text, auto_number',
      },
    ]);
  });
});
