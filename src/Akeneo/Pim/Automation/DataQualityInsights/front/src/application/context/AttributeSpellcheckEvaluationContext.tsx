import React, {createContext, FC, useContext} from "react";

import {Attribute} from "@akeneo-pim-community/data-quality-insights/src/domain";
import {
  initialSpellcheckEvaluationState,
  SpellcheckEvaluationState,
  useSpellcheckEvaluationState,
} from "../../infrastructure/hooks/AttributeEditForm/useSpellcheckEvaluationState";


export const AttributeSpellcheckEvaluationContext = createContext<SpellcheckEvaluationState>({
  evaluation: initialSpellcheckEvaluationState,
  refresh: () => (new Promise<void>(() => {}))
});

AttributeSpellcheckEvaluationContext.displayName = 'AttributeSpellcheckEvaluationContext';

export const useAttributeSpellcheckEvaluationContext = (): SpellcheckEvaluationState => {
  return useContext(AttributeSpellcheckEvaluationContext);
}

type ProviderProps = {
  attribute: Attribute;
};

export const AttributeSpellcheckEvaluationContextProvider: FC<ProviderProps> = ({children, attribute}) => {
  const state = useSpellcheckEvaluationState(attribute.code);

  return (
      <AttributeSpellcheckEvaluationContext.Provider value={state}>
        {children}
      </AttributeSpellcheckEvaluationContext.Provider>
  );
}
