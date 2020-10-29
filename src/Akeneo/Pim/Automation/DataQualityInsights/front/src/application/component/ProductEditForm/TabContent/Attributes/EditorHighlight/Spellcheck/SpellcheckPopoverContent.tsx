import React, {FunctionComponent, useCallback} from 'react';
import {HighlightElement, setEditorContent, WidgetElement} from '../../../../../../helper';
import {hidePopoverAction} from '../../../../../../../infrastructure/reducer';
import {useDispatch} from 'react-redux';
import {useFetchIgnoreTextIssue} from '../../../../../../../infrastructure/hooks';

const __ = require('oro/translator');

const SUGGESTIONS_LIMIT = 5;

export interface SpellcheckPopoverContentProps {
  highlight: HighlightElement | null;
  widget: WidgetElement | null;
}

const SpellcheckPopoverContent: FunctionComponent<SpellcheckPopoverContentProps> = ({highlight, widget}) => {
  const dispatchAction = useDispatch();
  const {dispatchIgnoreTextIssue} = useFetchIgnoreTextIssue();
  const mistake = highlight && highlight.mistake ? highlight.mistake : null;

  const handleSuggestionClick = useCallback(
    (suggestion: string) => {
      if (!widget || !mistake) {
        dispatchAction(hidePopoverAction());
        return;
      }

      const start = mistake.globalOffset;
      const end = mistake.globalOffset + mistake.text.length;

      setEditorContent(widget.editor, widget.content, suggestion, start, end);
      dispatchAction(hidePopoverAction());
    },
    [widget, mistake]
  );

  const handleIgnoreClick = useCallback(() => {
    if (!widget || !mistake) {
      dispatchAction(hidePopoverAction());
      return;
    }

    dispatchIgnoreTextIssue(mistake.text);
    dispatchAction(hidePopoverAction());
  }, [widget, mistake]);
  return (
    <>
      {mistake && (
        <div className="AknEditorHighlight-popover-content AknEditorHighlight-popover-content--spellcheck">
          <header>{__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.title')}</header>
          <div>
            <div className="AknEditorHighlight-popover-original">
              <p className="AknEditorHighlight-popover-original-title">
                {__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.original_text_title')}
              </p>
              <p className="AknSpellCheck-popover-original-item">{mistake.text}</p>
            </div>
            <hr />
            <div className="AknEditorHighlight-popover-suggestions">
              {mistake.suggestions && mistake.suggestions.length > 0 && (
                <>
                  <p className="AknEditorHighlight-popover-suggestions-title">
                    {__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.suggestions_title')}
                  </p>
                  <ul className="AknEditorHighlight-popover-suggestions-list">
                    {mistake.suggestions.slice(0, SUGGESTIONS_LIMIT).map((suggestion, index) => (
                      <li
                        key={`spellcheck-suggestion-${index}`}
                        className="AknEditorHighlight-popover-suggestions-item"
                        onClick={() => {
                          handleSuggestionClick(suggestion);
                        }}
                      >
                        <span>{suggestion}</span>
                      </li>
                    ))}
                  </ul>
                </>
              )}
            </div>
          </div>
          <footer>
            <button className="AknEditorHighlight-popover-ignore-button" onClick={handleIgnoreClick}>
              <span>
                {__(
                  'akeneo_data_quality_insights.product_edit_form.spellcheck_popover.ignore_all_suggestions_button_label'
                )}
              </span>
            </button>
          </footer>
        </div>
      )}
    </>
  );
};

export default SpellcheckPopoverContent;
