import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/record';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';

describe('akeneo > reference entity > domain > model > record > data --- record', () => {
  test('I can create a new RecordData with a RecordCode value', () => {
    expect(create(createCode('starck')).normalize()).toEqual('starck');
  });

  test('I cannot create a new RecordData with a value other than a RecordCode', () => {
    expect(() => {
      create(12);
    }).toThrow('RecordData expects a RecordCode as parameter to be created');
  });

  test('I can normalize a RecordData', () => {
    expect(denormalize('starck').normalize()).toEqual('starck');
    expect(denormalize(null).normalize()).toEqual(null);
  });

  test('I can test if two recordData are equal', () => {
    expect(denormalize('starck').equals(denormalize('starck'))).toEqual(true);
    expect(denormalize('starck').equals(denormalize('dyson'))).toEqual(false);
    expect(denormalize('starck').equals('starck')).toEqual(false);
    expect(denormalize(null).equals(denormalize(null))).toEqual(true);
    expect(denormalize(null).equals(denormalize('dyson'))).toEqual(false);
  });
});
