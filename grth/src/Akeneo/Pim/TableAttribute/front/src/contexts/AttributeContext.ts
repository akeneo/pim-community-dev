import React from 'react';
import {TableAttribute} from '../models';

type AttributeContextState = {
  attribute?: TableAttribute;
  setAttribute: (tableAttribute: TableAttribute) => void;
};

export const AttributeContext = React.createContext<AttributeContextState>({
  // eslint-disable-next-line @typescript-eslint/no-empty-function
  setAttribute: /* istanbul ignore next */ () => {},
});

export const useAttributeContext = () => {
  return React.useContext(AttributeContext);
};
