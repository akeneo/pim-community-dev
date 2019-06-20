import {reducer} from 'akeneoassetmanager/application/reducer/attribute/type/image';

describe('akeneo > asset family > application > reducer > attribute --- edit', () => {
  test('I can update the max file size property of the attribute', () => {
    const state = {type: 'image'};
    const newState = reducer(state, 'max_file_size', '12.3');

    expect(newState).toEqual({
      type: 'image',
      max_file_size: '12.3',
    });
  });

  test('I can update the allowed extensions property of the attribute', () => {
    const state = {type: 'image'};
    const newState = reducer(state, 'allowed_extensions', ['gif', 'png']);

    expect(newState).toEqual({
      type: 'image',
      allowed_extensions: ['gif', 'png'],
    });
  });

  test('It ignores invalid additional_property', () => {
    const state = {type: 'image'};
    const newState = reducer(state, 'michel', ['gif', 'png']);

    expect(newState).toBe(state);
  });
});
