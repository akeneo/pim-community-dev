import {validateAutoNumber} from '../validateAutoNumber';
import {PROPERTY_NAMES} from '../../models';

describe('validateAutoNumber', () => {
  it('should not add violation for valid digitsMin and numberMin', () => {
    expect(validateAutoNumber({type: PROPERTY_NAMES.AUTO_NUMBER, digitsMin: 3, numberMin: 2}, 'path')).toHaveLength(0);
  });

  it('should add violation for empty digitsMin', () => {
    expect(validateAutoNumber({type: PROPERTY_NAMES.AUTO_NUMBER, digitsMin: null, numberMin: 2}, 'path')).toHaveLength(
      1
    );
  });

  it('should add violation for empty numberMin', () => {
    expect(validateAutoNumber({type: PROPERTY_NAMES.AUTO_NUMBER, digitsMin: 3, numberMin: null}, 'path')).toHaveLength(
      1
    );
  });
});
