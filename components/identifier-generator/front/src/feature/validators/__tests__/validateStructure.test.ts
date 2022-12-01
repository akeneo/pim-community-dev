import {validateStructure} from '../validateStructure';
import {PROPERTY_NAMES, Structure} from '../../models';

describe('validateStructure', () => {
  it('should not add violation for valid structure', () => {
    expect(
      validateStructure(
        [
          {
            type: PROPERTY_NAMES.FREE_TEXT,
            string: 'AKN',
          },
        ],
        'structure'
      )
    ).toHaveLength(0);
  });

  it('should not add violations for structure properties', () => {
    expect(
      validateStructure(
        [
          {
            type: PROPERTY_NAMES.FREE_TEXT,
            string: '',
          },
        ],
        'structure'
      )
    ).toHaveLength(1);
  });

  it('should add a violation when there are no properties', () => {
    expect(validateStructure([], 'structure')).toEqual([
      {path: 'structure', message: 'The structure must contain at least one property'},
    ]);
  });

  it('should add a violation when the property is unknown', () => {
    expect(
      validateStructure(
        [
          {
            type: 'unknown',
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
