import React, {createContext, FC} from 'react';

import {AttributeOption} from '../model';
import {EditingOptionContextState, useEditingOptionContextState} from '../hooks/useEditingOptionContextState';

export const EditingOptionContext = createContext<EditingOptionContextState>({
    option: null,
    addRef: () => {},
    removeRef: () => {},
});
EditingOptionContext.displayName = 'EditingOptionContext';

type EditingOptionContextProviderProps = {
    option: AttributeOption;
};

export const EditingOptionContextProvider: FC<EditingOptionContextProviderProps> = ({children, option}) => {
    const initialState = useEditingOptionContextState(option);

    return (
        <EditingOptionContext.Provider value={initialState}>{children}</EditingOptionContext.Provider>
    );
};

