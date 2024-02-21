import React, {createContext, FC, useContext} from 'react';

import {AttributeOption} from '../model';
import {EditingOptionContextState, useEditingOptionContextState} from '../hooks/useEditingOptionContextState';

export const EditingOptionContext = createContext<EditingOptionContextState>({
  option: null,
  addRef: () => {},
  removeRef: () => {},
});
EditingOptionContext.displayName = 'EditingOptionContext';

export const useEditingOptionContext = (): EditingOptionContextState => {
  return useContext(EditingOptionContext);
};

type EditingOptionContextProviderProps = {
  option: AttributeOption;
};

export const EditingOptionContextProvider: FC<EditingOptionContextProviderProps> = ({children, option}) => {
  const initialState = useEditingOptionContextState(option);

  return <EditingOptionContext.Provider value={initialState}>{children}</EditingOptionContext.Provider>;
};
