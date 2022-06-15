import {isBooleanReplacementValues} from './BooleanReplacementValues';

test('it can tell if something is BooleanReplacementValues', () => {
  expect(
    isBooleanReplacementValues({
      true: ['oui'],
      false: ['non'],
      null: ['n/a'],
    })
  ).toBe(true);

  expect(isBooleanReplacementValues({foo: ['bar', 'baz']})).toBe(false);
  expect(isBooleanReplacementValues(null)).toBe(false);
  expect(isBooleanReplacementValues(undefined)).toBe(false);
  expect(isBooleanReplacementValues('')).toBe(false);
  expect(isBooleanReplacementValues({})).toBe(false);
  expect(
    isBooleanReplacementValues({
      true: 'oui',
      false: 'non',
      null: 'n/a',
    })
  ).toBe(false);
});
