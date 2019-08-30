import {
    createEmptyPrefix,
    createPrefixFromNormalized,
    createPrefixFromString,
    isValidPrefix,
    normalizePrefix,
    prefixStringValue,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';

describe('akeneo > attribute > domain > model > attribute > type > media-link --- prefix', () => {
  test('I can create a Prefix from normalized', () => {
    expect(normalizePrefix(createPrefixFromNormalized('12'))).toEqual('12');
    expect(normalizePrefix(createPrefixFromNormalized(null))).toEqual(null);
    expect(prefixStringValue(createPrefixFromNormalized(null))).toEqual('');
    expect(normalizePrefix(createEmptyPrefix(null))).toEqual(null);
  });
  test('I can validate a Prefix', () => {
    expect(isValidPrefix(null)).toEqual(true);
    expect(isValidPrefix('12')).toEqual(true);
    expect(isValidPrefix('12.3')).toEqual(true);
    expect(isValidPrefix('12.3a')).toEqual(true);
    expect(isValidPrefix({test: 'toto'})).toEqual(false);
    expect(isValidPrefix(12)).toEqual(false);
  });
  test('I can create a Prefix from string', () => {
    expect(normalizePrefix(createPrefixFromString('12'))).toEqual('12');
    expect(normalizePrefix(createPrefixFromString(''))).toEqual(null);
    expect(prefixStringValue(createPrefixFromString(''))).toEqual('');
    expect(prefixStringValue(createPrefixFromString('12'))).toEqual('12');
    expect(() => createPrefixFromString({my: 'object'})).toThrow();
  });
});
