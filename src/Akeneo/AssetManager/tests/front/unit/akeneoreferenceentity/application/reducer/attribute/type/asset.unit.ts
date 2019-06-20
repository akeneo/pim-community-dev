import {reducer} from 'akeneoassetmanager/application/reducer/attribute/type/asset';

describe('akeneo > asset family > application > reducer > attribute > type > asset --- edit', () => {
  test('I call the asset reducer', () => {
    const state = {type: 'asset'};
    const newState = reducer(state, 'label', 'designer');

    expect(newState).toBe(state);
  });
});
