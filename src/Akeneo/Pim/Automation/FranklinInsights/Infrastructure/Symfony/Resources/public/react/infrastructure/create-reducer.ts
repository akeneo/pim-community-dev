import {Reducer} from 'react';
import {Action} from 'redux';

export const createReducer = <S, A extends Action>(
  initialState: S,
  handlers: {[action: string]: Reducer<S, A>}
): Reducer<S, A> => (state = initialState, action) =>
  handlers.hasOwnProperty(action.type) ? handlers[action.type](state, action) : state;
