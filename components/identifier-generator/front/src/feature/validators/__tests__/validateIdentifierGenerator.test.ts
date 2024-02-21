import {validateIdentifierGenerator} from '../validateIdentifierGenerator';
import {PROPERTY_NAMES} from '../../models';

describe('validateIdentifierGenerator', () => {
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
              type: PROPERTY_NAMES.FREE_TEXT,
              string: 'AKN',
            },
          ],
        },
        ''
      )
    ).toHaveLength(0);
  });
});
