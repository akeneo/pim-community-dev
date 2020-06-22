import React, {createContext, FC, useContext} from 'react';
import {
    AttributeOptionsContextState,
    initialAttributeOptionsContextState,
    useAttributeOptionsContextState
} from "../hooks/useAttributeOptionsContextState";

export const AttributeOptionsContext = createContext<AttributeOptionsContextState>(initialAttributeOptionsContextState);
AttributeOptionsContext.displayName = 'AttributeOptionsContext';

export const useAttributeOptionsContext = () => {
    return useContext(AttributeOptionsContext);
};
type ProviderProps = {
    attributeId: number;
}

export const AttributeOptionsContextProvider: FC<ProviderProps> = ({children, attributeId}) => {
    const state = useAttributeOptionsContextState(attributeId);

    return (
        <AttributeOptionsContext.Provider value={state}>
            {children}
        </AttributeOptionsContext.Provider>
    );
}