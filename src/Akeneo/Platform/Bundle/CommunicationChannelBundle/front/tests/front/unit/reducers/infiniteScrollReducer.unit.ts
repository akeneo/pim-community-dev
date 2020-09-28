import {
  infiniteScrollFetchingResults,
  infiniteScrollFirstResultsFetched,
  infiniteScrollNextResultsFetched,
  infiniteScrollResultsNotFetched,
} from '../../../../src/actions/infiniteScrollActions';
import {reducer, initialState} from '../../../../src/reducers/infiniteScrollReducer';

it('handles INFINITE_SCROLL_FETCHING_RESULTS action', () => {
  const action = infiniteScrollFetchingResults();

  const newState = reducer(initialState, action);

  expect(newState).toStrictEqual({
    items: [],
    isFetching: true,
    hasError: false,
    lastAppend: false,
  });
});

it('handles INFINITE_SCROLL_FIRST_RESULTS_FETCHED action', () => {
  const action = infiniteScrollFirstResultsFetched([
    {
      title: 'item scrollable',
      description: 'description item scrollable',
    },
    {
      title: 'item scrollable 2',
      description: 'description item scrollable 2',
    },
  ]);

  const newState = reducer(initialState, action);

  expect(newState).toStrictEqual({
    items: [
      {
        title: 'item scrollable',
        description: 'description item scrollable',
      },
      {
        title: 'item scrollable 2',
        description: 'description item scrollable 2',
      },
    ],
    isFetching: false,
    hasError: false,
    lastAppend: false,
  });
});

it('handles to set the last append to false when INFINITE_SCROLL_FIRST_RESULTS_FETCHED is dispatched', () => {
  const initialState = {
    items: [],
    isFetching: false,
    hasError: false,
    lastAppend: true,
  };
  const action = infiniteScrollFirstResultsFetched([
    {
      title: 'item scrollable',
      description: 'description item scrollable',
    },
    {
      title: 'item scrollable 2',
      description: 'description item scrollable 2',
    },
  ]);

  const newState = reducer(initialState, action);

  expect(newState).toStrictEqual({
    items: [
      {
        title: 'item scrollable',
        description: 'description item scrollable',
      },
      {
        title: 'item scrollable 2',
        description: 'description item scrollable 2',
      },
    ],
    isFetching: false,
    hasError: false,
    lastAppend: false,
  });
});

it('handles INFINITE_SCROLL_NEXT_RESULTS_FETCHED action', () => {
  const initialState = {
    items: [
      {
        title: 'item scrollable',
        description: 'description item scrollable',
      },
      {
        title: 'item scrollable 2',
        description: 'description item scrollable 2',
      },
    ],
    isFetching: false,
    hasError: false,
    lastAppend: false,
  };
  const action = infiniteScrollNextResultsFetched(
    [
      {
        title: 'item scrollable 3',
        description: 'description item scrollable 3',
      },
    ],
    true
  );

  const newState = reducer(initialState, action);

  expect(newState).toStrictEqual({
    items: [
      {
        title: 'item scrollable',
        description: 'description item scrollable',
      },
      {
        title: 'item scrollable 2',
        description: 'description item scrollable 2',
      },
      {
        title: 'item scrollable 3',
        description: 'description item scrollable 3',
      },
    ],
    isFetching: false,
    hasError: false,
    lastAppend: true,
  });
});

it('handles INFINITE_SCROLL_RESULTS_NOT_FETCHED action', () => {
  const action = infiniteScrollResultsNotFetched();

  const newState = reducer(initialState, action);

  expect(newState).toStrictEqual({
    items: [],
    isFetching: false,
    hasError: true,
    lastAppend: false,
  });
});

it('throw an error when the action is not found', () => {
  expect(() => reducer(initialState, {type: 'ACTION_NOT_FOUND'})).toThrowError();
});
