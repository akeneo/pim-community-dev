import {Reducer} from 'react';
import {
  Actions,
  INFINITE_SCROLL_FETCHING_RESULTS,
  INFINITE_SCROLL_FIRST_RESULTS_FETCHED,
  INFINITE_SCROLL_NEXT_RESULTS_FETCHED,
  INFINITE_SCROLL_RESULTS_NOT_FETCHED,
} from '../actions/infiniteScrollActions';

type State = {
  items: any[];
  isFetching: boolean;
  hasError: boolean;
  lastAppend: boolean;
};

const reducer: Reducer<State, Actions> = (state, action) => {
  switch (action.type) {
    case INFINITE_SCROLL_FETCHING_RESULTS:
      return {...state, isFetching: true};
    case INFINITE_SCROLL_FIRST_RESULTS_FETCHED:
      return {...state, items: action.payload.items, isFetching: false, lastAppend: false};
    case INFINITE_SCROLL_NEXT_RESULTS_FETCHED:
      return {
        ...state,
        items: [...state.items, ...action.payload.items],
        lastAppend: action.payload.lastAppend,
        isFetching: false,
      };
    case INFINITE_SCROLL_RESULTS_NOT_FETCHED:
      return {...state, items: [], isFetching: false, hasError: true};
    default:
      throw new Error('Action for the list not found');
  }
};

const initialState: State = {
  items: [],
  isFetching: false,
  hasError: false,
  lastAppend: false,
};

export {reducer, initialState};
