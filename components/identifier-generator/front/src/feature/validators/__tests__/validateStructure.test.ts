import {validateStructure} from '../validateStructure';
import {AbbreviationType, PROPERTY_NAMES, Structure} from '../../models';

describe('validateStructure', () => {
  it('should not add violation for valid structure', () => {
    expect(
      validateStructure(
        [
          {
            type: PROPERTY_NAMES.FREE_TEXT,
            string: 'AKN',
          },
          {
            type: PROPERTY_NAMES.FAMILY,
            process: {
              type: AbbreviationType.NO,
            },
          },
        ],
        'structure'
      )
    ).toHaveLength(0);
  });

  it('should add violation for wrong structure property', () => {
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

    expect(
      validateStructure(
        [
          {
            type: PROPERTY_NAMES.AUTO_NUMBER,
            digitsMin: null,
            numberMin: 1,
          },
        ],
        'structure'
      )
    ).toHaveLength(1);

    expect(
      validateStructure(
        [
          {
            type: PROPERTY_NAMES.AUTO_NUMBER,
            digitsMin: 2,
            numberMin: null,
          },
          {
            type: PROPERTY_NAMES.SIMPLE_SELECT,
            attributeCode: 'color',
            locale: null,
            scope: null,
            process: {type: null},
          },
        ],
        'structure'
      )
    ).toHaveLength(2);
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
        message:
          'The property type "unknown" is unknown. Please choose one of the following: free_text, auto_number, family, simple_select, reference_entity',
      },
    ]);
  });
});
