import React, {FC, useCallback} from 'react';
import HighlightPopoverDisclosure, {HighlightPopoverDisclosureProps} from '../Popover/HighlightPopoverDisclosure';
import {HighlightElement, MistakeElement} from '../../../../helper';
import {useLocaleContext} from '../../../../context/LocaleContext';
import {useSpellcheckContentContext} from '../../../../context/Spellcheck/SpellcheckContentContext';
import analyzeSpellingInterface from '../../../../helper/Spellcheck/analyzeSpelling.interface';
import refreshSpellingInterface from '../../../../helper/Spellcheck/refreshSpelling.interface';

type SpellcheckPopoverDisclosureProps = HighlightPopoverDisclosureProps & {
  setLocale: (locale: string | null) => void;
  setContent: (content: string | null) => void;
  setMistake: (mistake: MistakeElement | null) => void;
  setAnalyze: (analyze: analyzeSpellingInterface) => void;
  setRefreshAnalysis: (refreshAnalysis: refreshSpellingInterface) => void;
};

const SpellcheckPopoverDisclosure: FC<SpellcheckPopoverDisclosureProps> = ({
  setLocale,
  setContent,
  setMistake,
  setActiveHighlight,
  setActiveElement,
  setAnalyze,
  setRefreshAnalysis,
  ...props
}) => {
  const {locale} = useLocaleContext();
  const {content, analyze, refreshAnalysis} = useSpellcheckContentContext();

  const setPopoverContext = useCallback(
    (
      locale: string | null,
      content: string | null,
      mistake: MistakeElement | null,
      analyze: analyzeSpellingInterface,
      refreshAnalysis: refreshSpellingInterface
    ) => {
      setLocale(locale);
      setContent(content);
      setAnalyze(() => analyze);
      setRefreshAnalysis(() => refreshAnalysis);
      setMistake(mistake);
    },
    [setLocale, setContent, setAnalyze, setMistake]
  );

  const handleSetActiveHighlight = useCallback(
    (highlight: HighlightElement | null) => {
      setActiveHighlight(highlight);

      if (highlight) {
        setPopoverContext(locale, content, highlight.mistake, analyze, refreshAnalysis);
      }
    },
    [setActiveHighlight, setPopoverContext, locale, content, analyze, refreshAnalysis]
  );

  return (
    <HighlightPopoverDisclosure
      {...props}
      setActiveHighlight={handleSetActiveHighlight}
      setActiveElement={setActiveElement}
    />
  );
};

export default SpellcheckPopoverDisclosure;
