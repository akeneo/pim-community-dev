import React, {createContext, FC, useContext} from 'react';

import {Attribute} from '@akeneo-pim-community/data-quality-insights/src/domain';
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

type ProviderProps = {
  attribute: Attribute;
};

export const AttributeOptionsListContextProvider: FC<ProviderProps> = ({children, attribute}) => {
  const state = useAttributeOptionsList(attribute);

  return <AttributeOptionsListContext.Provider value={state}>{children}</AttributeOptionsListContext.Provider>;
};
