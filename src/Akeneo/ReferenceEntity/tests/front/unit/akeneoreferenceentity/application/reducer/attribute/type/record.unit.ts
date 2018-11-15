import {reducer} from 'akeneoreferenceentity/application/reducer/attribute/type/record';

describe('akeneo > reference entity > application > reducer > attribute > type > record --- edit', () => {
  test('I call the record reducer', () => {
    const state = {type: 'record'};
    const newState = reducer(state, 'label', 'designer');

    expect(newState).toBe(state);
  });
});
