import React, {FC} from 'react';

type DismissButtonProps = {
  handleClick: (event: React.MouseEvent) => void;
};

const DismissButton: FC<DismissButtonProps> = ({children, handleClick}) => {
  return (
    <button className="AknEditorHighlight-popover-ignore-button" onClick={handleClick}>
      <span>{children}</span>
    </button>
  );
};

export default DismissButton;
