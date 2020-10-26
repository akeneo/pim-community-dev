import React, {FC, useCallback} from 'react';
import HighlightPopoverDisclosure, {HighlightPopoverDisclosureProps} from '../Popover/HighlightPopoverDisclosure';
import {HighlightElement, MistakeElement} from '../../../../helper';
import {useLocaleContext} from '../../../../context/LocaleContext';
import {useSpellcheckContentContext} from '../../../../context/Spellcheck/SpellcheckContentContext';
import analyzeSpellingInterface from '../../../../helper/Spellcheck/analyzeSpelling.interface';

type SpellcheckPopoverDisclosureProps = HighlightPopoverDisclosureProps & {
  setLocale: (locale: string | null) => void;
  setContent: (content: string | null) => void;
  setMistake: (mistake: MistakeElement | null) => void;
  setAnalyze: (analyze: analyzeSpellingInterface) => void;
};

const SpellcheckPopoverDisclosure: FC<SpellcheckPopoverDisclosureProps> = ({
  setLocale,
  setContent,
  setMistake,
  setActiveHighlight,
  setActiveElement,
  setAnalyze,
  ...props
}) => {
  const {locale} = useLocaleContext();
  const {content, analyze} = useSpellcheckContentContext();

  const setPopoverContext = useCallback(
    (
      locale: string | null,
      content: string | null,
      mistake: MistakeElement | null,
      analyze: analyzeSpellingInterface
    ) => {
      setLocale(locale);
      setContent(content);
      setAnalyze(() => analyze);
      setMistake(mistake);
    },
    [setLocale, setContent, setAnalyze, setMistake]
  );

  const handleSetActiveHighlight = useCallback(
    (highlight: HighlightElement | null) => {
      setActiveHighlight(highlight);

      if (highlight) {
        setPopoverContext(locale, content, highlight.mistake, analyze);
      }
    },
    [setActiveHighlight, setPopoverContext, locale, content]
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
