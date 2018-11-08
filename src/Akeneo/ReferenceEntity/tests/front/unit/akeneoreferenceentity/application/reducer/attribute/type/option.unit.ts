import {reducer} from 'akeneoreferenceentity/application/reducer/attribute/type/option';

describe('akeneo > reference entity > application > reducer > attribute > type > option --- edit', () => {
  test('I call the option reducer', () => {
    const state = {type: 'option'};
    const newState = reducer(state, 'label', 'designer');

    expect(newState).toBe(state);
  });
});
