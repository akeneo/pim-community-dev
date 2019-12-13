import {createContext, Dispatch, useContext} from 'react';
import {Actions} from './actions/dashboard-actions';
import {State, initialState} from './reducers/dashboard-reducer';

export const DashboardStateContext = createContext<[State, Dispatch<Actions>]>([initialState, () => undefined]);

export const useDashboardState = (): [State, Dispatch<Actions>] => useContext(DashboardStateContext);
