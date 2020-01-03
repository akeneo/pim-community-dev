import React, {createContext, Dispatch, useContext, PropsWithChildren, useReducer} from 'react';
import {Actions} from './actions/connections-actions';
import {State, initialState, reducer} from './reducers/connections-reducer';

const StateContext = createContext<State | undefined>(undefined);
const DispatchContext = createContext<Dispatch<Actions> | undefined>(undefined);

export const ConnectionsProvider = ({children}: PropsWithChildren<{}>) => {
    const [state, dispatch] = useReducer(reducer, initialState);

    return (
        <StateContext.Provider value={state}>
            <DispatchContext.Provider value={dispatch}>{children}</DispatchContext.Provider>
        </StateContext.Provider>
    );
};

export const useConnectionsState = () => {
    const context = useContext(StateContext);
    if (undefined === context) {
        throw new Error('useConnectionsState must be used within a ConnectionsProvider');
    }

    return context;
};

export const useConnectionsDispatch = () => {
    const context = useContext(DispatchContext);
    if (undefined === context) {
        throw new Error('useConnectionsDispatch must be used within a ConnectionsProvider');
    }

    return context;
};
