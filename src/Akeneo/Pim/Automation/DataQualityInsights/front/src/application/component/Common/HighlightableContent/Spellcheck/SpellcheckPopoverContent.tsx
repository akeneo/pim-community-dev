import React, {FC, useCallback} from 'react';

import {MistakeElement} from '../../../../helper';
import DismissButton from '../Popover/Content/DismissButton';
import SuggestionsList from '../Popover/Content/SuggestionsList';
import Separator from '../Popover/Content/Separator';
import OriginalText from '../Popover/Content/OriginalText';
import Footer from '../Popover/Content/Footer';
import HighlightPopoverContent from '../Popover/HighlightPopoverContent';
import {useSpellcheckPopoverContext} from '../../../../context/Spellcheck/SpellcheckPopoverContext';
import {useHighlightPopoverContext} from '../../../../context/HighlightPopoverContext';

const translate = require('oro/translator');

const SUGGESTIONS_LIMIT = 5;
const SUGGESTION_BASE_ID = 'spellcheck-suggestion';

type SpellcheckPopoverProps = {
  locale: string | null;
  content: string | null;
  mistake: MistakeElement | null;
};

const SpellcheckPopoverContent: FC<SpellcheckPopoverProps> = ({locale, content, mistake}) => {
  const {apply, ignore, refreshAnalysis} = useSpellcheckPopoverContext();
  const {activeElement, hide} = useHighlightPopoverContext();

  const handleApplySuggestion = useCallback(
    suggestion => {
      if (activeElement && mistake && content) {
        const start = mistake.globalOffset;
        const end = mistake.globalOffset + mistake.text.length;

        apply(activeElement, suggestion, content, start, end);
        hide();
      }
    },
    [apply, hide, mistake, content, activeElement]
  );

  const handleIgnoreMistake = useCallback(() => {
    if (mistake && locale && ignore && refreshAnalysis && hide) {
      ignore(mistake.text, locale);
      refreshAnalysis();
      hide();
    }
  }, [ignore, refreshAnalysis, hide, mistake, locale]);

  return (
    <>
      {mistake && (
        <HighlightPopoverContent
          title={translate('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.title')}
          classList={['AknEditorHighlight-popover-content--spellcheck']}
        >
          <div>
            <OriginalText
              title={translate('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.original_text_title')}
            >
              {mistake.text}
            </OriginalText>
            <Separator />
            <SuggestionsList
              suggestions={mistake.suggestions.slice(0, SUGGESTIONS_LIMIT)}
              title={translate('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.suggestions_title')}
              apply={handleApplySuggestion}
              baseId={SUGGESTION_BASE_ID}
            />
          </div>
          <Footer>
            <DismissButton handleClick={handleIgnoreMistake}>
              {translate(
                'akeneo_data_quality_insights.product_edit_form.spellcheck_popover.ignore_all_suggestions_button_label'
              )}
            </DismissButton>
          </Footer>
        </HighlightPopoverContent>
      )}
    </>
  );
};

export default SpellcheckPopoverContent;
