import {
  errorsReducer,
  errorsReceived,
  errorsRemovedAll,
  selectErrors,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';

/**
 *  REDUCER TESTS
 */

test('It ignores other commands', () => {
  const state = {};
  const newState = errorsReducer(state, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toMatchObject(state);
});

test('It should generate a default state', () => {
  const newState = errorsReducer(undefined, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toMatchObject([]);
});

test('It should add errors', () => {
  const state = {};
  const errors = [
    {
      attribute: 'packshot',
      locale: null,
      message: 'This value is not valid.',
      channel: null,
    },
  ];
  const newState = errorsReducer(state, {
    type: 'ERRORS_RECEIVED',
    errors,
  });

  expect(newState).toEqual(errors);
});

test('It should remove all errors', () => {
  const state = {
    attribute: 'packshot',
    locale: null,
    message: 'This value is not valid.',
    channel: null,
  };
  const errors = [];
  const newState = errorsReducer(state, {
    type: 'ERRORS_REMOVED_ALL',
    errors,
  });

  expect(newState).toEqual(errors);
});

/**
 *  ACTION CREATORS TESTS
 */

test('It should have an action to add errors', () => {
  const errors = [
    {
      attribute: 'packshot',
      locale: null,
      message: 'This value is not valid.',
      channel: null,
    },
  ];
  const expectedAction = {
    type: 'ERRORS_RECEIVED',
    errors,
  };
  expect(errorsReceived(errors)).toMatchObject(expectedAction);
});

test('It should have an action to remove all errors', () => {
  const expectedAction = {
    type: 'ERRORS_REMOVED_ALL',
    errors: [],
  };

  expect(errorsRemovedAll()).toMatchObject(expectedAction);
});

/**
 *  SELECTORS TESTS
 */

test('It should be able to select the errors', () => {
  const errors = [
    {
      attribute: 'packshot',
      locale: null,
      message: 'This value is not valid.',
      channel: null,
    },
  ];
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null},
    values: [],
    errors: errors,
  };

  expect(selectErrors(state)).toEqual(errors);
});
