import {validateStructure} from '../StructureValidator';

describe('StructureValidator', () => {
  it('should not add violation for valid structure', () => {
    expect(
      validateStructure(
        [
          {
            name: 'FreeText',
            value: 'AKN',
          },
        ],
        'structure'
      )
    ).toHaveLength(0);
  });

  it('should add a violation when there are no properties', () => {
    expect(validateStructure([], 'structure')).toEqual([
      {path: 'structure', message: 'You need at least one property'},
    ]);
  });
});
