import React, {FC, useCallback} from 'react';
import {useAttributeEditFormContext} from '../../../context/AttributeEditFormContext';
import useSpellcheckOptionsListState from '../../../../infrastructure/hooks/AttributeEditForm/useSpellcheckOptionsListState';
import useSpellcheckPopoverState, {
  useSpellcheckPopoverProps,
} from '../../../../infrastructure/hooks/Common/Spellcheck/useSpellcheckPopoverState';
import applySuggestionOnLabel from '../../../helper/AttributeEditForm/Spellcheck/applySuggestionOnLabel';
import fetchIgnoreOptionIssue from '../../../../infrastructure/fetcher/AttributeEditForm/fetchIgnoreOptionIssue';
import SpellcheckContentContextProvider from '../../../context/Spellcheck/SpellcheckContentContext';
import SpellcheckElement from '../../Common/HighlightableContent/Spellcheck/SpellcheckElement';
import ActiveHighlightsOnIntersection from '../../Common/HighlightableContent/ActiveHighlightsOnIntersection';
import UpdateHighlightsOnInputChange from '../../Common/HighlightableContent/UpdateHighlightsOnInputChange';
import SpellcheckPopoverDisclosure from '../../Common/HighlightableContent/Spellcheck/SpellcheckPopoverDisclosure';
import SpellcheckPopover from '../../Common/HighlightableContent/Spellcheck/SpellcheckPopover';
import {useAttributeSpellcheckEvaluationContext} from '../../../context/AttributeSpellcheckEvaluationContext';
import {ATTRIBUTE_EDIT_FORM_UPDATED} from '../../../constant';

const SPELLCHECK_OPTION_ELEMENT_BASE_ID = 'attribute-option-spellcheck';

const SpellcheckOptionsList: FC = () => {
  const {attribute} = useAttributeEditFormContext();
  const {elements, editingOption} = useSpellcheckOptionsListState();
  const {refresh} = useAttributeSpellcheckEvaluationContext();
  const handleIgnore = useCallback(
    (text: string, locale: string) => {
      (async () => {
        if (editingOption === null) {
          return;
        }

        await fetchIgnoreOptionIssue(text, locale, attribute.code, editingOption.code);
        await refresh();
        window.dispatchEvent(new CustomEvent(ATTRIBUTE_EDIT_FORM_UPDATED));
      })();
    },
    [editingOption]
  );
  const popoverState = useSpellcheckPopoverState({
    apply: applySuggestionOnLabel,
    ignore: handleIgnore,
  });
  const popoverProps = useSpellcheckPopoverProps(popoverState);

  return (
    <>
      {Object.entries(elements).map(([key, {locale, element}]) => (
        <SpellcheckContentContextProvider key={key} locale={locale} element={element}>
          <SpellcheckElement baseId={SPELLCHECK_OPTION_ELEMENT_BASE_ID}>
            <ActiveHighlightsOnIntersection />
            <UpdateHighlightsOnInputChange />
            <SpellcheckPopoverDisclosure element={element} {...popoverState} />
          </SpellcheckElement>
        </SpellcheckContentContextProvider>
      ))}

      <SpellcheckPopover {...popoverProps} />
    </>
  );
};

export default SpellcheckOptionsList;
