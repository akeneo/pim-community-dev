import {reducer} from 'akeneoassetmanager/application/reducer/attribute/type/media-file';

describe('akeneo > asset family > application > reducer > attribute --- edit', () => {
  test('I can update the max file size property of the attribute', () => {
    const state = {type: 'media_file'};
    const newState = reducer(state, 'max_file_size', '12.3');

    expect(newState).toEqual({
      type: 'media_file',
      max_file_size: '12.3',
    });
  });

  test('I can update the allowed extensions property of the attribute', () => {
    const state = {type: 'media_file'};
    const newState = reducer(state, 'allowed_extensions', ['gif', 'png']);

    expect(newState).toEqual({
      type: 'media_file',
      allowed_extensions: ['gif', 'png'],
    });
  });

  test('I can update the media type property of the attribute', () => {
    const state = {type: 'media_file'};
    const newState = reducer(state, 'media_type', ['pdf']);

    expect(newState).toEqual({
      type: 'media_file',
      media_type: ['pdf'],
    });
  });

  test('It ignores invalid additional_property', () => {
    const state = {type: 'media_file'};
    const newState = reducer(state, 'michel', ['gif', 'png']);

    expect(newState).toBe(state);
  });
});
