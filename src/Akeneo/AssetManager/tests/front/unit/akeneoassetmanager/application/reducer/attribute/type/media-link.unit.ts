import {reducer} from 'akeneoassetmanager/application/reducer/attribute/type/media-link';

describe('akeneo > asset family > application > reducer > attribute --- edit', () => {
  test('I can update the prefix property of the attribute', () => {
    const state = {type: 'media_link'};
    const newState = reducer(state, 'prefix', 'google.fr');

    expect(newState).toEqual({
      type: 'media_link',
      prefix: 'google.fr',
    });
  });

  test('I can update the suffix property of the attribute', () => {
    const state = {type: 'media_link'};
    const newState = reducer(state, 'suffix', null);

    expect(newState).toEqual({
      type: 'media_link',
      suffix: null,
    });
  });

  test('I can update the media type property of the attribute', () => {
    const state = {type: 'media_link'};
    const newState = reducer(state, 'media_type', 'image');

    expect(newState).toEqual({
      type: 'media_link',
      media_type: 'image',
    });
  });

  test('It ignores invalid additional_property', () => {
    const state = {type: 'media_link'};
    const newState = reducer(state, 'michel', ['gif', 'png']);

    expect(newState).toBe(state);
  });
});
