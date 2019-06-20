import reducer from 'akeneoassetmanager/application/reducer/confirmDelete';

describe('akeneo > asset family > application > reducer --- confirmDelete', () => {
  test('I ignore other commands', () => {
    const state = {isActive: false, identifier: undefined, label: undefined};
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual(state);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({isActive: false});
  });

  test('I can start the delete modal', () => {
    const state = {isActive: false, identifier: undefined, label: undefined};
    const modalState = reducer(state, {
      type: 'DELETE_MODAL_OPEN',
      isActive: true,
    });

    expect(modalState).toEqual({
      isActive: true,
      identifier: undefined,
      label: undefined,
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
