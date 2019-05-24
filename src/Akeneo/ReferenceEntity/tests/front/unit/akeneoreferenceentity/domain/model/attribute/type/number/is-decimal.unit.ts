import {DecimalsAllowed} from 'akeneoreferenceentity/domain/model/attribute/type/number/decimals-allowed';

describe('akeneo > attribute > domain > model > attribute > type > number --- DecimalsAllowed', () => {
  test('I can create a DecimalsAllowed from normalized', () => {
    expect(DecimalsAllowed.createFromNormalized(false).normalize()).toEqual(false);
    expect(DecimalsAllowed.createFromNormalized(true).normalize()).toEqual(true);
    expect(() => DecimalsAllowed.createFromNormalized('true')).toThrow();
  });
  test('I can validate a DecimalsAllowed', () => {
    expect(DecimalsAllowed.isValid(true)).toEqual(true);
    expect(DecimalsAllowed.isValid(false)).toEqual(true);
    expect(DecimalsAllowed.isValid('12')).toEqual(false);
    expect(DecimalsAllowed.isValid('1')).toEqual(false);
    expect(DecimalsAllowed.isValid(1)).toEqual(false);
    expect(DecimalsAllowed.isValid(0)).toEqual(false);
    expect(DecimalsAllowed.isValid(undefined)).toEqual(false);
    expect(DecimalsAllowed.isValid({})).toEqual(false);
  });
  test('I can create a DecimalsAllowed from boolean', () => {
    expect(DecimalsAllowed.createFromBoolean(true).booleanValue()).toEqual(true);
    expect(DecimalsAllowed.createFromBoolean(false).booleanValue()).toEqual(false);
  });
});
