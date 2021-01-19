import React, {createContext, FC, useContext} from 'react';
import ignoreSpellingIssueInterface from '../../helper/Spellcheck/ignoreSpellingIssue.interface';
import applySpellingSuggestionInterface from '../../helper/Spellcheck/applySpellingSuggestion.interface';
import analyzeSpellingInterface from '../../helper/Spellcheck/analyzeSpelling.interface';
import refreshSpellingInterface from '../../helper/Spellcheck/refreshSpelling.interface';

export type SpellcheckPopoverContextState = {
  apply: applySpellingSuggestionInterface;
  ignore: ignoreSpellingIssueInterface;
  analyze: analyzeSpellingInterface;
  refreshAnalysis: refreshSpellingInterface;
};

export const SpellcheckPopoverContext = createContext<SpellcheckPopoverContextState>({
  apply: () => {},
  ignore: () => {},
  analyze: () => {},
  refreshAnalysis: () => {},
});

SpellcheckPopoverContext.displayName = 'SpellcheckPopoverContext';

export const useSpellcheckPopoverContext = (): SpellcheckPopoverContextState => {
  return useContext(SpellcheckPopoverContext);
};

type ProviderProps = SpellcheckPopoverContextState;
export const SpellcheckPopoverContextProvider: FC<ProviderProps> = ({children, ...initialState}) => {
  return <SpellcheckPopoverContext.Provider value={initialState}>{children}</SpellcheckPopoverContext.Provider>;
};
