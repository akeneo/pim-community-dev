import React, {FC} from 'react';

import useSpellcheckLabelsListState from '../../../../infrastructure/hooks/AttributeEditForm/useSpellcheckLabelsListState';
import SpellcheckElement from '../../Common/HighlightableContent/Spellcheck/SpellcheckElement';
import UpdateHighlightsOnInputChange from '../../Common/HighlightableContent/UpdateHighlightsOnInputChange';
import SpellcheckContentContextProvider from '../../../context/Spellcheck/SpellcheckContentContext';
import SpellcheckPopover from '../../Common/HighlightableContent/Spellcheck/SpellcheckPopover';
import ActiveHighlightsOnIntersection from '../../Common/HighlightableContent/ActiveHighlightsOnIntersection';
import useSpellcheckPopoverState, {
  useSpellcheckPopoverProps,
} from '../../../../infrastructure/hooks/Common/Spellcheck/useSpellcheckPopoverState';
import SpellcheckPopoverDisclosure from '../../Common/HighlightableContent/Spellcheck/SpellcheckPopoverDisclosure';
import applySuggestionOnLabel from '../../../helper/AttributeEditForm/Spellcheck/applySuggestionOnLabel';
import {useAttributeEditFormContext} from '../../../context/AttributeEditFormContext';
import fetchIgnoreTextIssue from '../../../../infrastructure/fetcher/AttributeEditForm/fetchIgnoreTextIssue';
import {ATTRIBUTE_EDIT_FORM_UPDATED} from '../../../constant';

const SPELLCHECK_LABEL_ELEMENT_BASE_ID = 'attribute-label-spellcheck';

type SpellcheckLabelsListProps = {};

const SpellcheckLabelsList: FC<SpellcheckLabelsListProps> = () => {
  const {attribute, renderingId} = useAttributeEditFormContext();
  const {elements} = useSpellcheckLabelsListState(renderingId);
  const popoverState = useSpellcheckPopoverState({
    apply: applySuggestionOnLabel,
    ignore: (text: string, locale: string) => {
      (async () => {
        await fetchIgnoreTextIssue(text, locale, attribute.code);
        window.dispatchEvent(new CustomEvent(ATTRIBUTE_EDIT_FORM_UPDATED));
      })();
    },
  });
  const popoverProps = useSpellcheckPopoverProps(popoverState);

  return (
    <>
      {Object.entries(elements).map(([key, element]) => (
        <SpellcheckContentContextProvider key={key} locale={element.dataset.locale || null} element={element}>
          <SpellcheckElement baseId={SPELLCHECK_LABEL_ELEMENT_BASE_ID}>
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

export default SpellcheckLabelsList;
