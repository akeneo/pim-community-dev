import React, {createContext, Dispatch, useContext, PropsWithChildren, useReducer} from 'react';
import {WrongCredentialsCombinationsActions} from './actions/wrong-credentials-combinations-actions';
import {initialState, reducer} from './reducers/wrong-credentials-combination-reducer';
import {WrongCredentialsCombinations} from '../model/wrong-credentials-combinations';

const StateContext = createContext<WrongCredentialsCombinations | undefined>(undefined);
const DispatchContext = createContext<Dispatch<WrongCredentialsCombinationsActions> | undefined>(undefined);

export const WrongCredentialsCombinationsProvider = ({children}: PropsWithChildren<{}>) => {
    const [state, dispatch] = useReducer(reducer, initialState);

    return (
        <StateContext.Provider value={state}>
            <DispatchContext.Provider value={dispatch}>{children}</DispatchContext.Provider>
        </StateContext.Provider>
    );
};

export const useWrongCredentialsCombinationsState = () => {
    const context = useContext(StateContext);
    if (undefined === context) {
        throw new Error(
            'useWrongCredentialsCombinationsState must be used within a WrongCredentialsCombinationsProvider'
        );
    }

    return context;
};

export const useWrongCredentialsCombinationsDispatch = () => {
    const context = useContext(DispatchContext);
    if (undefined === context) {
        throw new Error(
            'useWrongCredentialsCombinationsDispatch must be used within a WrongCredentialsCombinationsProvider'
        );
    }

    return context;
};
