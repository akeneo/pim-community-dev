import React, {createContext, FC, useContext} from 'react';

import {Attribute} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {AttributeSpellcheckEvaluationContextProvider} from './AttributeSpellcheckEvaluationContext';
import {AttributeOptionsListContextProvider} from './AttributeOptionsListContext';

export type AttributeEditFormContextState = {
  attribute: Attribute;
  renderingId: number;
};

export const AttributeEditFormContext = createContext<AttributeEditFormContextState>({
  attribute: {
    code: '',
    type: '',
    group: '',
    meta: {
      id: 0,
    },
  },
  renderingId: 0,
});

AttributeEditFormContext.displayName = 'AttributeEditFormContext';

export const useAttributeEditFormContext = (): AttributeEditFormContextState => {
  return useContext(AttributeEditFormContext);
};

type ProviderProps = {
  attribute: Attribute;
  renderingId: number;
};

export const AttributeEditFormContextProvider: FC<ProviderProps> = ({children, ...initialState}) => {
  const {attribute} = initialState;

  return (
    <AttributeEditFormContext.Provider value={initialState}>
      <AttributeOptionsListContextProvider>
        <AttributeSpellcheckEvaluationContextProvider attribute={attribute}>
          {children}
        </AttributeSpellcheckEvaluationContextProvider>
      </AttributeOptionsListContextProvider>
    </AttributeEditFormContext.Provider>
  );
};
