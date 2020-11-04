import {useState} from 'react';

import useHighlightPopoverState, {
  HighlightPopoverActionState,
  HighlightPopoverInitialState,
  HighlightPopoverState,
  useHighlightPopoverProps,
} from '../useHighlightPopoverState';
import {SpellcheckPopoverProps} from '../../../../application/component/Common/HighlightableContent/Spellcheck/SpellcheckPopover';
import {MistakeElement} from '../../../../application/helper';
import ignoreSpellingIssueInterface from '../../../../application/helper/Spellcheck/ignoreSpellingIssue.interface';
import applySpellingSuggestionInterface from '../../../../application/helper/Spellcheck/applySpellingSuggestion.interface';
import analyzeSpellingInterface from '../../../../application/helper/Spellcheck/analyzeSpelling.interface';

export type SpellcheckPopoverActionState = HighlightPopoverActionState & {
  apply: applySpellingSuggestionInterface;
  ignore: ignoreSpellingIssueInterface;
  analyze: analyzeSpellingInterface;
  setMistake: (mistake: MistakeElement | null) => void;
  setLocale: (locale: string | null) => void;
  setContent: (content: string | null) => void;
  setAnalyze: (analyze: analyzeSpellingInterface) => void;
};

export type SpellcheckPopoverState = HighlightPopoverState &
  SpellcheckPopoverActionState & {
    locale: string | null;
    content: string | null;
    mistake: MistakeElement | null;
  };

export type SpellcheckPopoverInitialState = HighlightPopoverInitialState & {
  apply?: applySpellingSuggestionInterface;
  ignore?: ignoreSpellingIssueInterface;
};

export const useSpellcheckPopoverProps = (state: SpellcheckPopoverState): SpellcheckPopoverProps => {
  const {
    apply,
    ignore,
    locale,
    content,
    mistake,
    setMistake,
    setLocale,
    setContent,
    analyze,
    setAnalyze,
    ...highlightPopoverState
  } = state;
  const highlightPopoverProps = useHighlightPopoverProps(highlightPopoverState);

  return {
    ...highlightPopoverProps,
    apply,
    ignore,
    analyze,
    locale,
    content,
    mistake,
  };
};

const useSpellcheckPopoverState = (initialPopoverState?: SpellcheckPopoverInitialState): SpellcheckPopoverState => {
  const [locale, setLocale] = useState<string | null>(null);
  const [content, setContent] = useState<string | null>(null);
  const [mistake, setMistake] = useState<MistakeElement | null>(null);
  const [analyze, setAnalyze] = useState<analyzeSpellingInterface>(() => () => {});

  const {apply, ignore, ...initialHighlightPopoverState} = initialPopoverState || {};
  const popoverState = useHighlightPopoverState({
    ...(initialHighlightPopoverState || {}),
  });

  return {
    ...popoverState,
    apply: apply !== undefined ? apply : () => {},
    ignore: ignore !== undefined ? ignore : () => {},
    locale,
    content,
    mistake,
    setLocale,
    setContent,
    setMistake,
    analyze,
    setAnalyze,
  };
};

export default useSpellcheckPopoverState;
