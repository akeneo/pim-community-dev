import React, {createContext, FC} from 'react';
import {
    AttributeOptionsContextState,
    initialAttributeOptionsContextState,
    useAttributeOptionsContextState
} from '../hooks/useAttributeOptionsContextState';

export const AttributeOptionsContext = createContext<AttributeOptionsContextState>(initialAttributeOptionsContextState);
AttributeOptionsContext.displayName = 'AttributeOptionsContext';

export const AttributeOptionsContextProvider: FC = ({children}) => {
    const state = useAttributeOptionsContextState();

    return (
        <AttributeOptionsContext.Provider value={state}>
            {children}
        </AttributeOptionsContext.Provider>
    );
};
