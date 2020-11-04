import React, {FC} from 'react';
import HighlightPopover, {HighlightPopoverProps} from '../Popover/HighlightPopover';
import SpellcheckPopoverContent from './SpellcheckPopoverContent';
import {SpellcheckPopoverContextProvider} from '../../../../context/Spellcheck/SpellcheckPopoverContext';
import useAriaLabel from '../../../../../infrastructure/hooks/Common/Spellcheck/useAriaLabel';
import {MistakeElement} from '../../../../helper';
import ignoreSpellingIssueInterface from '../../../../helper/Spellcheck/ignoreSpellingIssue.interface';
import applySpellingSuggestionInterface from '../../../../helper/Spellcheck/applySpellingSuggestion.interface';
import analyzeSpellingInterface from '../../../../helper/Spellcheck/analyzeSpelling.interface';

const POPOVER_BASE_ID = 'attribute-label-spellcheck-popover';

export type SpellcheckPopoverProps = HighlightPopoverProps & {
  apply: applySpellingSuggestionInterface;
  ignore: ignoreSpellingIssueInterface;
  analyze: analyzeSpellingInterface;
  locale: string | null;
  content: string | null;
  mistake: MistakeElement | null;
};

const SpellcheckPopover: FC<SpellcheckPopoverProps> = props => {
  const {locale, content, mistake, apply, ignore, analyze, ...popoverProps} = props;
  const ariaLabel = useAriaLabel(mistake);

  return (
    <SpellcheckPopoverContextProvider apply={apply} ignore={ignore} analyze={analyze}>
      <HighlightPopover
        {...popoverProps}
        baseId={POPOVER_BASE_ID}
        ariaLabel={ariaLabel}
        hideOnClickOutside={true}
        hideOnEsc={true}
      >
        <SpellcheckPopoverContent locale={locale} content={content} mistake={mistake} />
      </HighlightPopover>
    </SpellcheckPopoverContextProvider>
  );
};

export default SpellcheckPopover;
