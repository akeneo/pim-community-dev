import React, {createContext, FC, useContext} from 'react';
import useAttributeContextState from '../hooks/useAttributeContextState';

export type AttributeContextState = {
  attributeId: number;
  autoSortOptions: boolean;
  toggleAutoSortOptions: () => void;
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

type AttributeContextProviderProps = {
  attributeId: number;
  autoSortOptions: boolean;
};

export const AttributeContextProvider: FC<AttributeContextProviderProps> = ({
  children,
  attributeId,
  autoSortOptions,
}) => {
  const state = useAttributeContextState(attributeId, autoSortOptions);

  return <AttributeContext.Provider value={state}>{children}</AttributeContext.Provider>;
};
