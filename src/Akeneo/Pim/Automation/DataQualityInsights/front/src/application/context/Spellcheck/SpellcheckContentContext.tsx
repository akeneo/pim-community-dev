import React, {FC} from 'react';
import {LocaleContextProvider, LocaleContextState, useLocaleContext} from '../LocaleContext';
import {
  HighlightableContentContextProvider,
  HighlightableContentContextState,
  useHighlightableContentContext,
} from '../HighlightableContentContext';
import SpellcheckAnalysisContextProvider, {useSpellcheckAnalysisContext} from './SpellcheckAnalysisContext';
import {SpellcheckAnalysisState} from '../../../infrastructure/hooks/Common/Spellcheck/useFetchSpellcheckAnalysis';

type SpellcheckContentContextState = LocaleContextState & HighlightableContentContextState & SpellcheckAnalysisState;

type SpellcheckContentContextProviderProps = {
  element: HTMLElement;
  locale: string | null;
};

export const useSpellcheckContentContext = (): SpellcheckContentContextState => {
  const contentContext = useHighlightableContentContext();
  const localeContext = useLocaleContext();
  const spellcheckAnalysisContext = useSpellcheckAnalysisContext();

  return {
    ...contentContext,
    ...localeContext,
    ...spellcheckAnalysisContext,
  };
};

const SpellcheckContentContextProvider: FC<SpellcheckContentContextProviderProps> = ({children, element, locale}) => {
  return (
    <>
      {locale !== null && (
        <LocaleContextProvider locale={locale}>
          <HighlightableContentContextProvider element={element}>
            <SpellcheckAnalysisContextProvider>{children}</SpellcheckAnalysisContextProvider>
          </HighlightableContentContextProvider>
        </LocaleContextProvider>
      )}
    </>
  );
};

export default SpellcheckContentContextProvider;
