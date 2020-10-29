import React, {FC} from 'react';

type SuggestionProps = {
  handleSelection: (suggestion: string) => void;
  suggestion: string;
};

const Suggestion: FC<SuggestionProps> = ({children, handleSelection, suggestion}) => {
  return (
    <li className="AknEditorHighlight-popover-suggestions-item" onClick={() => handleSelection(suggestion)}>
      <span>{children}</span>
    </li>
  );
};

export default Suggestion;
