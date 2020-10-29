import React, {FC} from 'react';

import {useHighlightsContainerContext} from '../../../context/HighlightsContainerContext';
import {HighlightElement} from '../../../helper';
import HighlightsList from './HighlightsList';
import MirrorElement from './MirrorElement';
import withPortal from '../Decorator/withPortal';

type HighlightsContainerProps = {
  highlights: HighlightElement[];
};

export const HighlightsContainer: FC<HighlightsContainerProps> = ({children, highlights = []}) => {
  const {position, dimension} = useHighlightsContainerContext();

  return (
    <div className="AknEditorHighlight-wrapper">
      <div
        className={'AknEditorHighlight-highlights AknEditorHighlight--box-reset'}
        style={{
          top: position.top,
          left: position.left,
          width: dimension.width,
          height: dimension.height,
        }}
      >
        <div
          className={`AknEditorHighlight-highlights-wrapper`}
          style={{
            width: dimension.width,
            height: dimension.height,
          }}
        >
          <HighlightsList highlights={highlights} />
          {children}
        </div>
        <MirrorElement />
      </div>
    </div>
  );
};

export const HighlightsContainerWithPortal = withPortal(HighlightsContainer);
