import {createContext, Dispatch, useContext} from 'react';
import {Actions} from './actions/apps-actions';
import {State} from './reducers/apps-reducer';

export const DashboardStateContext = createContext<[State, Dispatch<Actions>]>([{}, () => undefined]);

export const useDashboardState = (): [State, Dispatch<Actions>] => useContext(DashboardStateContext);
