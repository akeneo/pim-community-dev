import {String, Boolean, Null, Collection} from 'pimfront/product-grid/domain/model/filter/value';

describe('>>>DOMAIN --- model - value - string', () => {
  test('I can create a new string value', () => {
    expect(String.fromValue('hey!').getValue()).toBe('hey!');
  });

  test('I can compare with other values', () => {
    expect(String.fromValue('hey!').equals(String.fromValue('hey!'))).toBe(true);
    expect(String.fromValue('hey!').equals(String.fromValue('boo'))).toBe(false);
    expect(String.fromValue('hey!').equals(Boolean.fromValue(false))).toBe(false);
  });

  test("I can see if it's empty", () => {
    expect(String.fromValue('hey!').isEmpty()).toBe(false);
    expect(String.fromValue('').isEmpty()).toBe(true);
  });

  test("I can get it's serialized value", () => {
    expect(String.fromValue('hey!').toString()).toBe('hey!');
  });
});

describe('>>>DOMAIN --- model - value - boolean', () => {
  test('I can create a new boolean value', () => {
    expect(Boolean.fromValue(false).getValue()).toBe(false);
  });

  test('I can compare with other values', () => {
    expect(Boolean.fromValue(false).equals(Boolean.fromValue(false))).toBe(true);
    expect(Boolean.fromValue(false).equals(Boolean.fromValue(true))).toBe(false);
    expect(Boolean.fromValue(false).equals(String.fromValue('false'))).toBe(false);
  });

  test("I can see if it's empty", () => {
    expect(Boolean.fromValue(true).isEmpty()).toBe(false);
    expect(Boolean.fromValue(false).isEmpty()).toBe(false);
  });

  test("I can get it's serialized value", () => {
    expect(Boolean.fromValue(true).toString()).toBe('true');
    expect(Boolean.fromValue(false).toString()).toBe('false');
  });
});

describe('>>>DOMAIN --- model - value - null', () => {
  test('I can create a new null value', () => {
    expect(Null.fromValue(null).getValue()).toBe(null);
  });

  test('I can compare with other values', () => {
    expect(Null.fromValue(null).equals(Null.fromValue(null))).toBe(true);
    expect(Null.fromValue(null).equals(Boolean.fromValue(true))).toBe(false);
  });

  test("I can see if it's empty", () => {
    expect(Null.fromValue(null).isEmpty()).toBe(true);
  });

  test("I can get it's serialized value", () => {
    expect(Null.fromValue(null).toString()).toBe('null');
  });
});

describe('>>>DOMAIN --- model - value - collection', () => {
  test('I can create a new collection value', () => {
    expect(Collection.fromValue<string>(['foo', 'bar']).getValue()).toEqual(['foo', 'bar']);
  });

  test('I can compare with other values', () => {
    expect(Collection.fromValue<string>(['foo', 'bar']).equals(Collection.fromValue<string>(['foo', 'bar']))).toBe(
      true
    );
    expect(Collection.fromValue<string>(['baz', 'bar']).equals(Collection.fromValue<string>(['foo', 'bar']))).toBe(
      false
    );
    expect(Collection.fromValue<string>(['foo', 'bar']).equals(Boolean.fromValue(true))).toBe(false);
  });

  test("I can see if it's empty", () => {
    expect(Collection.fromValue<string>(['foo', 'bar']).isEmpty()).toBe(false);
    expect(Collection.fromValue<string>([]).isEmpty()).toBe(true);
  });

  test("I can get it's serialized value", () => {
    expect(Collection.fromValue<string>(['foo', 'bar']).toString()).toBe('foo, bar');
  });
});
