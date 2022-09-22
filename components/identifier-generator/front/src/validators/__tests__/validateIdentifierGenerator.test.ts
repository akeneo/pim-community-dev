import {validateIdentifierGenerator} from '../validateIdentifierGenerator';
import {PROPERTY_NAMES} from '../../models';

describe('IdentifierGeneratorValidator', () => {
  it('should not add violation for valid identifier generator', () => {
    expect(
      validateIdentifierGenerator(
        {
          code: 'aValidCode',
          target: 'sku',
          conditions: [],
          delimiter: '-',
          labels: {},
          structure: [
            {
              propertyName: PROPERTY_NAMES.FREE_TEXT,
              value: 'AKN',
            },
          ],
        },
        ''
      )
    ).toHaveLength(0);
  });
});
