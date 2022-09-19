import {validateIdentifierGenerator} from '../IdentifierGeneratorValidator';

describe('IdentifierGeneratorValidator', () => {
  it('should not add violation for valid identifier generator', () => {
    expect(
      validateIdentifierGenerator(
        {
          code: 'aValidCode',
          target: 'sku',
          conditions: [],
          delimiter: {value: '-'},
          labelCollection: {},
          structure: [],
        },
        ''
      )
    ).toHaveLength(0);
  });
});
