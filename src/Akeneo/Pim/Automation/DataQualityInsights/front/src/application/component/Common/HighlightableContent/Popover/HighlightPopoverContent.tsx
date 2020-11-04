import React, {FC} from 'react';

import Header from './Content/Header';

type HighlightPopoverContentProps = {
  title: string;
  classList: string[];
};

const HighlightPopoverContent: FC<HighlightPopoverContentProps> = ({children, title, classList}) => {
  return (
    <div className={['AknEditorHighlight-popover-content'].concat(...classList).join(' ')}>
      <Header>{title}</Header>
      {children}
    </div>
  );
};

export default HighlightPopoverContent;
