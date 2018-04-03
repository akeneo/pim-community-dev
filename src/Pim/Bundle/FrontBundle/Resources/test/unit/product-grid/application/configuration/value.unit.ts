import provideValueFor from 'pimfront/product-grid/application/configuration/value';
import {Boolean, Null, String, Collection} from 'pimfront/product-grid/domain/model/filter/value';

describe('>>>APPLICATION --- configuration - value', () => {
  test('It provide me a false boolean value', () => {
    const value = provideValueFor(false);

    expect(value.equals(Boolean.fromValue(false))).toBe(true);
  });

  test('It provide me a true boolean value', () => {
    const value = provideValueFor(true);

    expect(value.equals(Boolean.fromValue(true))).toBe(true);
  });

  test('It provide me something else if the value is not boolean', () => {
    const value = provideValueFor('true');

    expect(value.equals(Boolean.fromValue(true))).toBe(false);
  });

  test('It provide me a string value', () => {
    const value = provideValueFor('my value');

    expect(value.equals(String.fromValue('my value'))).toBe(true);
  });

  test('It provide me a null value', () => {
    const value = provideValueFor(null);

    expect(value.equals(Null.fromValue(null))).toBe(true);
  });

  test('It provide me a collection value', () => {
    const value = provideValueFor(['my', 'value']);

    expect(value.equals(Collection.fromValue(['my', 'value']))).toBe(true);
  });

  test('It provide me nothing if not supported', () => {
    const value = provideValueFor({
      something: 'not supported',
    });

    expect(value).toBe(null);
  });
});
