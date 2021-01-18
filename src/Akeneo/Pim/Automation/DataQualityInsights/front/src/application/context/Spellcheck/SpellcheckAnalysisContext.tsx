import React, {createContext, FC, useContext} from 'react';
import {useHighlightableContentContext} from '../HighlightableContentContext';
import useFetchSpellcheckAnalysis, {
  SpellcheckAnalysisState,
} from '../../../infrastructure/hooks/Common/Spellcheck/useFetchSpellcheckAnalysis';
import {useLocaleContext} from '../LocaleContext';

type SpellcheckAnalysisContextState = SpellcheckAnalysisState;

export const SpellcheckAnalysisContext = createContext<SpellcheckAnalysisContextState>({
  analysis: [],
  isLoading: false,
  analyze: () => {},
  refreshAnalysis: () => {},
});

SpellcheckAnalysisContext.displayName = 'SpellcheckAnalysisContext';

export const useSpellcheckAnalysisContext = (): SpellcheckAnalysisContextState => {
  return useContext(SpellcheckAnalysisContext);
};

const SpellcheckAnalysisContextProvider: FC = ({children}) => {
  const {analyzableContent, isActive} = useHighlightableContentContext();
  const {locale} = useLocaleContext();
  const spellcheckAnalysisState = useFetchSpellcheckAnalysis(analyzableContent, locale, isActive);

  return (
    <SpellcheckAnalysisContext.Provider value={spellcheckAnalysisState}>{children}</SpellcheckAnalysisContext.Provider>
  );
};

export default SpellcheckAnalysisContextProvider;
