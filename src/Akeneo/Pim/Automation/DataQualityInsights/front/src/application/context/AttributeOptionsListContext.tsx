import React, {createContext, FC, useContext} from 'react';
import {
  AttributeOptionsListState,
  initialAttributeOptionsListState,
  useAttributeOptionsList,
} from '../../infrastructure/hooks/AttributeEditForm/useAttributeOptionsList';

export const AttributeOptionsListContext = createContext<AttributeOptionsListState>(initialAttributeOptionsListState);

AttributeOptionsListContext.displayName = 'AttributeOptionsListContext';

export const useAttributeOptionsListContext = (): AttributeOptionsListState => {
  return useContext(AttributeOptionsListContext);
};

export const AttributeOptionsListContextProvider: FC = ({children}) => {
  const state = useAttributeOptionsList();

  return <AttributeOptionsListContext.Provider value={state}>{children}</AttributeOptionsListContext.Provider>;
};
