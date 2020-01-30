import React, {FunctionComponent, useCallback} from "react";
import {useDispatch} from "react-redux";
import {MistakeElement, setEditorContent, WidgetElement} from "../../../../../../helper";
import {hidePopoverAction, updateWidgetContentAnalysis} from "../../../../../../../infrastructure/reducer";
import useFetchIgnoreTitleSuggestion
  from "../../../../../../../infrastructure/hooks/EditorHighlight/SuggestedTitle/useFetchIgnoreTitleSuggestion";

const __ = require("oro/translator");

const getSuggestion = (mistake: MistakeElement|null) => {
  if (mistake && mistake.suggestions && mistake.suggestions.length > 0) {
    return mistake.suggestions[0];
  }
  return null;
};

export interface SuggestedTitlePopoverContentProps {
  mistake: MistakeElement | null;
  widget: WidgetElement | null;
}

const SuggestedTitlePopoverContent: FunctionComponent<SuggestedTitlePopoverContentProps> = ({mistake, widget}) => {
  let suggestion: string|null = getSuggestion(mistake);
  const dispatchAction = useDispatch();

  const {dispatchIgnoreTitleSuggestion} = useFetchIgnoreTitleSuggestion();

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

  const handleIgnoreClick = useCallback((suggestion: string) => {
    if (!widget || !mistake) {
      dispatchAction(hidePopoverAction());
      return;
    }

    dispatchIgnoreTitleSuggestion(suggestion);
    dispatchAction(updateWidgetContentAnalysis(widget.id, []));
    dispatchAction(hidePopoverAction());
  }, [widget, mistake]);

  return (
    <>
      {(typeof suggestion === "string" && suggestion.length > 0) && (
        <div className="AknEditorHighlight-popover-content AknEditorHighlight-popover-content--title_suggestion">
          <header>{__('akeneo_data_quality_insights.product_edit_form.title_suggestion_popover.title')}</header>
          <div>
            <div className="AknEditorHighlight-popover-suggestions">
              <ul className="AknEditorHighlight-popover-suggestions-list">
                  <li className="AknEditorHighlight-popover-suggestions-item"
                      onClick={() => {handleSuggestionClick(suggestion as string);}}>
                    <span>{suggestion}</span>
                  </li>
              </ul>
            </div>
          </div>
          <footer>
            <button className="AknEditorHighlight-popover-ignore-button"
              onClick={() => handleIgnoreClick(suggestion as string)}
            >
              <span>{__('akeneo_data_quality_insights.product_edit_form.title_suggestion_popover.ignore_all_suggestions_button_label')}</span>
            </button>
          </footer>
        </div>
      )}
    </>
  );
};

export default SuggestedTitlePopoverContent;
