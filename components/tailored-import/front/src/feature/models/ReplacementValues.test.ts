import {filterEmptyValues, isReplacementValues} from './ReplacementValues';

test('it can filter out empty replacement values', () => {
  const values = {
    empty: [],
    foo: ['bar'],
    bar: ['baz', 'bzaaa'],
    anotherEmpty: [],
  };

  expect(filterEmptyValues(values)).toEqual({
    foo: ['bar'],
    bar: ['baz', 'bzaaa'],
  });
});

test('it can tell if something is ReplacementValues', () => {
  expect(isReplacementValues({})).toBe(true);
  expect(isReplacementValues({foo: ['bar']})).toBe(true);
  expect(isReplacementValues({foo: ['bar', 'baz']})).toBe(true);

  expect(isReplacementValues(null)).toBe(false);
  expect(isReplacementValues(undefined)).toBe(false);
  expect(isReplacementValues('')).toBe(false);
});
