import React from 'react';
import {TableAttribute} from '../models';

type AttributeContextState = {
  attribute?: TableAttribute;
  setAttribute: (tableAttribute: TableAttribute) => void;
};

export const AttributeContext = React.createContext<AttributeContextState>({
  setAttribute: () => {
    console.error('AttributeContext setAttribute can not be called');
  },
});

export const useAttributeContext = () => {
  return React.useContext(AttributeContext);
};
