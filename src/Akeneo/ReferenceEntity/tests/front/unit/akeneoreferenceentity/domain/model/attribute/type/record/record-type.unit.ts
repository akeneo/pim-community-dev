import {RecordType} from 'akeneoreferenceentity/domain/model/attribute/type/record/record-type';

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
});
