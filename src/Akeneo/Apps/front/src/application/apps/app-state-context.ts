import {createContext, Dispatch, useContext} from 'react';
import {Actions} from './actions/apps-actions';
import {State} from './reducers/apps-reducer';

export const AppsStateContext = createContext<[State, Dispatch<Actions>]>([{}, () => undefined]);

export const useAppsState = (): [State, Dispatch<Actions>] => useContext(AppsStateContext);
