import React, {FunctionComponent, useCallback} from "react";
import {MistakeElement, setEditorContent} from "../../../../../../domain";
import {hidePopoverAction} from "../../../../../../infrastructure/reducer";
import {useDispatch} from "react-redux";
import {useFetchIgnoreTextIssue, useGetSpellcheckWidget} from "../../../../../../infrastructure/hooks";

const __ = require("oro/translator");

const SUGGESTIONS_LIMIT = 5;

interface PopoverContentProps {
  mistake: MistakeElement | null;
  widgetId: string | null;
}


const PopoverContent: FunctionComponent<PopoverContentProps> = ({mistake, widgetId}) => {
  const dispatchAction = useDispatch();
  const widget = useGetSpellcheckWidget(widgetId);
  const {dispatchIgnoreTextIssue} = useFetchIgnoreTextIssue();

  const handleSuggestionClick = useCallback((suggestion: string) => {

    if (!widget || !mistake) {
      dispatchAction(hidePopoverAction());
      return;
    }

    const start = mistake.globalOffset;
    const end = mistake.globalOffset + mistake.text.length;

    setEditorContent(widget.editor, widget.content, suggestion, start, end);

    dispatchAction(hidePopoverAction());
  }, [widget, mistake]);

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
        <div>
          <header>{__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.title')}</header>
          <div>

            <div className="AknSpellCheck-popover-original">
              <p className="AknSpellCheck-popover-original-title">
                {__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.original_text_title')}
              </p>
              <p className="knSpellCheck-popover-original-item">{mistake.text}</p>
            </div>
            <hr />
            <div className="AknSpellCheck-popover-suggestions">
              {mistake.suggestions && mistake.suggestions.length > 0 && (
                <>
                  <p className="AknSpellCheck-popover-suggestions-title">
                    {__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.suggestions_title')}
                  </p>
                  <ul className="AknSpellCheck-popover-suggestions-list">
                    {mistake.suggestions
                      .slice(0, SUGGESTIONS_LIMIT)
                      .map((suggestion, index) => (
                        <li key={`suggestion-${index}`}
                            className="AknSpellCheck-popover-suggestions-item"
                            onClick={() => {handleSuggestionClick(suggestion);}}>
                          <span>{suggestion}</span>
                        </li>
                      ))}
                  </ul>
                </>
              )}
            </div>
          </div>
          <footer>
            <button className="AknSpellCheck-popover-ignore-button"
              onClick={handleIgnoreClick}
            >
              <span>{__('akeneo_data_quality_insights.product_edit_form.spellcheck_popover.ignore_all_suggestions_button_label')}</span>
            </button>
          </footer>
        </div>
      )}
    </>
  );
};

export default PopoverContent;
