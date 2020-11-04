import React, {FC} from 'react';

type OriginalTextProps = {
  title: string;
};

const OriginalText: FC<OriginalTextProps> = ({children, title}) => {
  return (
    <div className="AknEditorHighlight-popover-original">
      <p className="AknEditorHighlight-popover-original-title">{title}</p>
      <p className="AknSpellCheck-popover-original-item">{children}</p>
    </div>
  );
};

export default OriginalText;
