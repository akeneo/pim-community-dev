import React, {FC} from 'react';

import Suggestion from './Suggestion';

type SuggestionsListProps = {
  suggestions: string[];
  title: string;
  apply: (suggestion: string) => void;
  baseId: string;
};

const SuggestionsList: FC<SuggestionsListProps> = ({suggestions, title, apply, baseId = 'suggestion-'}) => {
  return (
    <div className="AknEditorHighlight-popover-suggestions">
      {suggestions.length > 0 && (
        <>
          <p className="AknEditorHighlight-popover-suggestions-title">{title}</p>
          <ul className="AknEditorHighlight-popover-suggestions-list">
            {suggestions.map((suggestion, index) => (
              <Suggestion key={`${baseId}-${index}`} suggestion={suggestion} handleSelection={apply}>
                {suggestion}
              </Suggestion>
            ))}
          </ul>
        </>
      )}
    </div>
  );
};

export default SuggestionsList;
