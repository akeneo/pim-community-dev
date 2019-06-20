import {IsTextarea} from 'akeneoassetmanager/domain/model/attribute/type/text/is-textarea';

describe('akeneo > attribute > domain > model > attribute > type > text --- IsTextarea', () => {
  test('I can create a IsTextarea from normalized', () => {
    expect(IsTextarea.createFromNormalized(false).normalize()).toEqual(false);
    expect(IsTextarea.createFromNormalized(true).normalize()).toEqual(true);
    expect(() => IsTextarea.createFromNormalized('true')).toThrow();
  });
  test('I can validate a IsTextarea', () => {
    expect(IsTextarea.isValid(true)).toEqual(true);
    expect(IsTextarea.isValid(false)).toEqual(true);
    expect(IsTextarea.isValid('12')).toEqual(false);
    expect(IsTextarea.isValid('1')).toEqual(false);
    expect(IsTextarea.isValid(1)).toEqual(false);
    expect(IsTextarea.isValid(0)).toEqual(false);
    expect(IsTextarea.isValid(undefined)).toEqual(false);
    expect(IsTextarea.isValid({})).toEqual(false);
  });
  test('I can create a IsTextarea from boolean', () => {
    expect(IsTextarea.createFromBoolean(true).booleanValue()).toEqual(true);
    expect(IsTextarea.createFromBoolean(false).booleanValue()).toEqual(false);
  });
});
