import React from 'react';
import {TableAttribute} from '../models';

type AttributeContextState = {
  attribute: TableAttribute | undefined;
  setAttribute: (tableAttribute: TableAttribute) => void;
};

export const AttributeContext = React.createContext<AttributeContextState | undefined>(undefined);

export const useAttributeContext = () => {
  const context = React.useContext(AttributeContext);

  if (undefined === context) {
    throw new Error('The AttributeContext was not initialized.');
  }

  return context;
};
