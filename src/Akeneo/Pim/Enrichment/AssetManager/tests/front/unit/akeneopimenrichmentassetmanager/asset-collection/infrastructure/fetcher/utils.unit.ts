import {
  isString,
  isObject,
  isBoolean,
  isNumber,
  isArray,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/utils';

test('It should verify if the value is a string', () => {
  expect(isString('packshot')).toEqual(true);
  expect(isString(null)).toEqual(false);
  expect(isString(undefined)).toEqual(false);
});

test('It should verify if the value is an object', () => {
  expect(isObject({en_US: 'Packshot'})).toEqual(true);
  expect(isObject(null)).toEqual(false);
  expect(isObject(undefined)).toEqual(false);
});

test('It should verify if the value is a boolean', () => {
  expect(isBoolean(true)).toEqual(true);
  expect(isBoolean(null)).toEqual(false);
  expect(isBoolean(undefined)).toEqual(false);
});

test('It should verify if the value is a number', () => {
  expect(isNumber(10)).toEqual(true);
  expect(isNumber(null)).toEqual(false);
  expect(isNumber(undefined)).toEqual(false);
});

test('It should verify if the value is a number', () => {
  expect(isArray(['scanners'])).toEqual(true);
  expect(isArray(null)).toEqual(false);
  expect(isArray(undefined)).toEqual(false);
});
