import reducer from 'akeneoassetmanager/application/reducer/state';

describe('akeneo > asset family > application > reducer --- edit-form', () => {
  const editFormReducer = reducer('entity', 'ENTITY_UPDATED', 'ENTITY_RECEIVED');
  test('I ignore other commands', () => {
    const state = {
      entity: {
        identifier: 'michel',
        label: 'Michel',
      },
    };
    const newState = editFormReducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can generate a default state', () => {
    const newState = editFormReducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({isDirty: false, originalData: ''});
  });

  test('I can receive original data', () => {
    const entity = {
      entity: {
        identifier: 'michel',
        label: 'Michel',
      },
    };

    const newState = editFormReducer(entity, {
      type: 'ENTITY_RECEIVED',
      entity,
    });

    expect(newState).toEqual({
      entity: {identifier: 'michel', label: 'Michel'},
      isDirty: false,
      originalData: JSON.stringify(entity),
    });
  });

  test('I can invalid the edit form', () => {
    const entity = {
      entity: {
        identifier: 'michel',
        label: 'Michel',
      },
    };

    const stateEntityReceived = editFormReducer(entity, {
      type: 'ENTITY_RECEIVED',
      entity,
    });

    const entityUpdated = {
      entity: {
        identifier: 'michel',
        label: 'Dave',
      },
    };

    const newState = editFormReducer(stateEntityReceived, {
      type: 'ENTITY_UPDATED',
      entityUpdated,
    });

    expect(newState).toEqual({
      entity: {identifier: 'michel', label: 'Michel'},
      isDirty: true,
      originalData: JSON.stringify(entity),
    });
  });
});
