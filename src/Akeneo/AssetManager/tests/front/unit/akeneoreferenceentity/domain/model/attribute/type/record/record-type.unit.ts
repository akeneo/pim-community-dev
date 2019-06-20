import {RecordType} from 'akeneoreferenceentity/domain/model/attribute/type/record/record-type';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

describe('akeneo > attribute > domain > model > attribute > type > record --- record type', () => {
  test('I can create a RecordType from normalized', () => {
    expect(RecordType.createFromNormalized('brand').normalize()).toEqual('brand');
    expect(RecordType.createFromNormalized(null).normalize()).toEqual(null);
    expect(() => new RecordType({my: 'object'})).toThrow();
  });

  test('I can validate a RecordType', () => {
    expect(RecordType.isValid('test')).toEqual(true);
    expect(RecordType.isValid(null)).toEqual(false);
    expect(RecordType.isValid(12)).toEqual(false);
    expect(RecordType.isValid(null)).toEqual(false);
    expect(RecordType.isValid({test: 'toto'})).toEqual(false);
  });

  test('I can create a RecordType from string', () => {
    expect(RecordType.createFromString('brand').stringValue()).toEqual('brand');
    expect(RecordType.createFromString('').stringValue()).toEqual('');
    expect(() => RecordType.createFromString({my: 'object'})).toThrow();
  });

  test('I can get the reference entity identifier', () => {
    expect(RecordType.createFromString('brand').getReferenceEntityIdentifier()).toEqual(createIdentifier('brand'));
    expect(() => RecordType.createFromNormalized(null).getReferenceEntityIdentifier()).toThrow();
  });

  test('I can test if a record type is equal to another one', () => {
    expect(RecordType.createFromString('brand').equals(RecordType.createFromString('brand'))).toBe(true);
    expect(RecordType.createFromString('brand').equals(RecordType.createFromString('designer'))).toBe(false);
    expect(RecordType.createFromNormalized(null).equals(RecordType.createFromString('designer'))).toBe(false);
    expect(RecordType.createFromNormalized(null).equals(RecordType.createFromNormalized(null))).toBe(true);
  });
});
