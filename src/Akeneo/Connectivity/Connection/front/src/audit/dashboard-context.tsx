import React, {createContext, Dispatch, useContext, PropsWithChildren, useReducer} from 'react';
import {Actions} from './actions/dashboard-actions';
import {State, initialState as defaultState, reducer} from './reducers/dashboard-reducer';

const StateContext = createContext<State | undefined>(undefined);
const DispatchContext = createContext<Dispatch<Actions> | undefined>(undefined);

export const DashboardProvider = ({children, initialState}: PropsWithChildren<{initialState?: State}>) => {
    const [state, dispatch] = useReducer(reducer, initialState || defaultState);

    return (
        <StateContext.Provider value={state}>
            <DispatchContext.Provider value={dispatch}>{children}</DispatchContext.Provider>
        </StateContext.Provider>
    );
};

export const useDashboardState = () => {
    const context = useContext(StateContext);
    if (undefined === context) {
        throw new Error('useDashboardState must be used within a DashboardProvider');
    }

    return context;
};

export const useDashboardDispatch = () => {
    const context = useContext(DispatchContext);
    if (undefined === context) {
        throw new Error('useDashboardDispatch must be used within a DashboardProvider');
    }

    return context;
};
