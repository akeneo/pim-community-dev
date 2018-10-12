import reducer from 'akeneoreferenceentity/application/reducer/confirmDelete';

describe('akeneo > reference entity > application > reducer --- confirmDelete', () => {
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

    expect(newState).toEqual({isActive: false});
  });

  test('I can start the delete modal', () => {
    const state = {isActive: false};
    const modalState = reducer(state, {
      type: 'DELETE_MODAL_OPEN',
      isActive: true,
    });

    expect(modalState).toEqual({
      isActive: true,
    });
  });

  test('I can confirm the deletion in the modal', () => {
    const state = {isActive: true};
    const modalState = reducer(state, {
      type: 'DELETE_MODAL_CLOSE',
      isActive: false,
    });

    expect(modalState).toEqual({
      isActive: false,
    });
  });

  test('I can cancel the deletion in the modal', () => {
    const state = {isActive: true};
    const modalState = reducer(state, {
      type: 'DELETE_MODAL_CANCEL',
      isActive: false,
    });

    expect(modalState).toEqual({
      isActive: false,
    });
  });

  test('I can dismiss the deletion in the modal', () => {
    const state = {isActive: true};
    const modalState = reducer(state, {
      type: 'DISMISS',
      isActive: false,
    });

    expect(modalState).toEqual({
      isActive: false,
    });
  });
});
