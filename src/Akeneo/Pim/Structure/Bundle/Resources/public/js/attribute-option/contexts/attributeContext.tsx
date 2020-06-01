import React, {createContext, FC, useContext} from 'react';
export type AttributeContextState = {
    attributeId: number;
};
export const AttributeContext = createContext<AttributeContextState | undefined>(undefined);
AttributeContext.displayName = 'AttributeContext';

export const useAttributeContext = () => {
    const attributeContext = useContext(AttributeContext);
    if (!attributeContext) {
        throw new Error('[AttributeContext]: attribute context has not been properly initiated');
    }

    return attributeContext;
};

export const AttributeContextProvider: FC<AttributeContextState> = ({children, ...attribute}) => {
    return (
        <AttributeContext.Provider value={attribute}>
            {children}
        </AttributeContext.Provider>
    );
};
