import {create, denormalize} from 'akeneoenrichedentity/domain/model/record/data/text';

describe('akeneo > enriched entity > domain > model > record > data --- text', () => {
  test('I can create a new TextData with a File value', () => {
    expect(create('nice value').normalize()).toEqual('nice value');
  });

  test('I cannot create a new TextData with a value other than a non empty string', () => {
    expect(() => {
      create(12);
    }).toThrow('TextData expect a non empty string as parameter to be created');
  });

  test('I can normalize a TextData', () => {
    expect(denormalize('awesome text').normalize()).toEqual('awesome text');
  });
});
