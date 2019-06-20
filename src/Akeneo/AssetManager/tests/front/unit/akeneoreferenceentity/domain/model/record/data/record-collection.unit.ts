import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/record-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';

describe('akeneo > reference entity > domain > model > record > data --- record collection', () => {
  test('I can create a new RecordData with a RecordCode collection value', () => {
    expect(create([]).normalize()).toEqual([]);
    expect(create([createCode('starck')]).normalize()).toEqual(['starck']);
    expect(create([createCode('starck'), createCode('dyson')]).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I cannot create a new RecordData with a value other than a RecordCode collection', () => {
    expect(() => {
      create(12);
    }).toThrow('RecordCollectionData expects an array of RecordCode as parameter to be created');
    expect(() => {
      create([12]);
    }).toThrow('RecordCollectionData expects an array of RecordCode as parameter to be created');
  });

  test('I can normalize a RecordData', () => {
    expect(denormalize(null).normalize()).toEqual([]);
    expect(denormalize(['starck']).normalize()).toEqual(['starck']);
    expect(denormalize(['starck', 'dyson']).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I can test if two recordData are equal', () => {
    expect(denormalize(['starck']).equals(denormalize(['starck']))).toEqual(true);
    expect(denormalize(['starck', 'dyson']).equals(denormalize(['starck', 'dyson']))).toEqual(true);
    expect(denormalize(['dyson', 'starck']).equals(denormalize(['starck', 'dyson']))).toEqual(false);
    expect(denormalize(['starck']).equals(denormalize(['dyson']))).toEqual(false);
    expect(denormalize(['starck']).equals(['starck'])).toEqual(false);
    expect(denormalize(null).equals(denormalize(null))).toEqual(true);
    expect(denormalize(null).equals(denormalize(['dyson']))).toEqual(false);
  });

  test('I can test if the record data is empty', () => {
    expect(denormalize(['starck']).isEmpty()).toEqual(false);
    expect(denormalize(null).isEmpty()).toEqual(true);
  });
});
