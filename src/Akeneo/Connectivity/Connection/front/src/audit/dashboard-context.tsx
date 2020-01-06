import React, {createContext, Dispatch, useContext, PropsWithChildren, useReducer} from 'react';
import {Actions} from './actions/dashboard-actions';
import {State, initialState, reducer} from './reducers/dashboard-reducer';

const StateContext = createContext<State | undefined>(undefined);
const DispatchContext = createContext<Dispatch<Actions> | undefined>(undefined);

export const DashboardProvider = ({children}: PropsWithChildren<{}>) => {
    const [state, dispatch] = useReducer(reducer, initialState);

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
