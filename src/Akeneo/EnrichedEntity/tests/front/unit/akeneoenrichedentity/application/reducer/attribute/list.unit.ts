import reducer from 'akeneoenrichedentity/application/reducer/attribute/list';

describe('akeneo > enriched entity > application > reducer > attribute --- list', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({attributes: [], openedAttribute: null});
  });

  test('I can receive an attribute list', () => {
    const newState = reducer(
      {attributes: [], openedAttribute: null},
      {
        type: 'ATTRIBUTE_LIST_UPDATED',
        attributes: ['description', 'name'],
      }
    );

    expect(newState).toEqual({attributes: ['description', 'name'], openedAttribute: null});
  });
});
