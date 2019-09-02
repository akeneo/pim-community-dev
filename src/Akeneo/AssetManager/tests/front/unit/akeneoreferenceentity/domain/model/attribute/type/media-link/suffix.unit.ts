import {
    createEmptySuffix,
    createSuffixFromNormalized,
    createSuffixFromString,
    isValidSuffix,
    normalizeSuffix,
    suffixStringValue,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';

describe('akeneo > attribute > domain > model > attribute > type > media-link --- suffix', () => {
  test('I can create a Suffix from normalized', () => {
    expect(normalizeSuffix(createSuffixFromNormalized('12'))).toEqual('12');
    expect(normalizeSuffix(createSuffixFromNormalized(null))).toEqual(null);
    expect(suffixStringValue(createSuffixFromNormalized(null))).toEqual('');
    expect(normalizeSuffix(createEmptySuffix(null))).toEqual(null);
  });
  test('I can validate a Suffix', () => {
    expect(isValidSuffix(null)).toEqual(true);
    expect(isValidSuffix('12')).toEqual(true);
    expect(isValidSuffix('12.3')).toEqual(true);
    expect(isValidSuffix('12.3a')).toEqual(true);
    expect(isValidSuffix({test: 'toto'})).toEqual(false);
    expect(isValidSuffix(12)).toEqual(false);
  });
  test('I can create a Suffix from string', () => {
    expect(normalizeSuffix(createSuffixFromString('12'))).toEqual('12');
    expect(normalizeSuffix(createSuffixFromString(''))).toEqual(null);
    expect(suffixStringValue(createSuffixFromString(''))).toEqual('');
    expect(suffixStringValue(createSuffixFromString('12'))).toEqual('12');
    expect(() => createSuffixFromString({my: 'object'})).toThrow();
  });
});
