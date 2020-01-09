import React, {FunctionComponent} from "react";
import {MistakeElement} from "../../../../../../domain";

const __ = require("oro/translator");

const SUGGESTIONS_LIMIT = 5;

interface PopoverContentProps {
  mistake: MistakeElement | null;
}

const PopoverContent: FunctionComponent<PopoverContentProps> = ({mistake}) => {
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
                            onClick={() => {alert(`handle apply suggestion ${suggestion}`)}}>
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
              onClick={()=> {alert('handle ignore suggestions');}}
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
